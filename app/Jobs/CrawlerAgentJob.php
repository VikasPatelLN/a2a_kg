<?php

namespace App\Jobs;

use App\Models\{Document, Mention, Source};
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\DomCrawler\Crawler as Dom;

class CrawlerAgentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Source $source, public int $maxDepth = 2, public int $maxPages = 50) {}

    public function handle(): void
    {
        $cfg = collect($this->source->config);
        $start = $cfg->get('start_url');
        if (!$start) return;

        ini_set('max_execution_time', 120);

        $client = new Client(['headers'=>['User-Agent'=>'A2A-KG']]);
        $startHost = parse_url($start, PHP_URL_HOST);
        $frontier = [[$start,0]]; $visited=[]; $count=0;

        while ($frontier && $count < $this->maxPages) {
            [$url,$depth]=array_shift($frontier);
            $u=rtrim($url,'/'); if(isset($visited[$u])||$depth>$this->maxDepth) continue; $visited[$u]=true;
            try{ $res=$client->get($u);}catch(\Throwable $e){continue;}
            $html=(string)$res->getBody(); $dom=new Dom($html,$u);
            $title = $dom->filter('title')->count() ? $dom->filter('title')->text('') : null;
            $text = trim(preg_replace('/\s+/', ' ', $dom->filter('body')->text('')));
            $doc = Document::firstOrCreate(['source_id'=>$this->source->id,'external_id'=>$u],[
                'title'=>$title,'content'=>$text,'metadata'=>['fetched_at'=>now()->toIso8601String()]
            ]);
            $this->mentions($doc);
            $count++;
            foreach($dom->filter('a[href]')->each(fn($n)=>$n->attr('href')) as $href){
                $abs=$this->abs($u,$href); if(!$abs) continue; if(parse_url($abs,PHP_URL_HOST)!==$startHost) continue; if(!isset($visited[$abs])) $frontier[]=[$abs,$depth+1];
            }
        }

        // Link this source's docs
        foreach($this->source->documents as $d){ LinkerAgentJob::dispatchSync($d); }
        GraphOpsAgentJob::dispatchSync();

    }

    protected function mentions(Document $doc): void
    {
        $c = $doc->content ?? '';
        preg_match_all('/\b([A-Z][a-z]+(?:\s+[A-Z][a-z]+)+)\b/u', $c, $m);
        foreach(array_unique($m[1]) as $cand){
            Mention::firstOrCreate(['document_id'=>$doc->id,'text'=>$cand],[ 'features'=>['rule'=>'CapWordSeq'] ]);
        }
    }

    protected function abs(string $base,string $href):?string
    {
        if(str_starts_with($href,'http')) return explode('#',$href)[0];
        if(str_starts_with($href,'//')){ $sch=parse_url($base,PHP_URL_SCHEME)?:'http'; return $sch.':'.$href; }
        if(str_starts_with($href,'/')){ $p=parse_url($base); return $p['scheme'].'://'.$p['host'].$href; }
        if(str_starts_with($href,'#')||str_starts_with($href,'mailto:')||str_starts_with($href,'javascript:')) return null;
        return rtrim(dirname($base),'/').'/'.$href;
    }
}
