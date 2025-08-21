<?php

namespace App\Jobs;

use App\Models\{Document, Mention, Entity, Triple};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Extractors\RuleBasedExtractor;

class LinkerAgentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Document $document) {}

    public function handle(): void
    {
        $doc=$this->document->fresh(['mentions']);
        $rb=new RuleBasedExtractor();

        foreach($doc->mentions as $m){ $ent=$this->resolve($m->text); $m->entity()->associate($ent)->save(); }
        $out=$rb->extract($doc->content); $this->upsert($out);
    }

    protected function resolve(string $name): Entity
    {
        $e=Entity::where('name',$name)->orWhereJsonContains('aliases',$name)->first();
        if($e) return $e;
        $best=Entity::orderByRaw('LENGTH(name)')->get()->sortBy(fn($x)=>levenshtein(mb_strtolower($x->name),mb_strtolower($name)))->first();
        if($best && levenshtein($best->name,$name)<=2) return $best;
        return Entity::create(['type'=>'Concept','name'=>$name]);
    }

    protected function upsert(array $out): void
    {
        $map=[]; foreach($out['entities']??[] as $e){ $ent=$this->resolve($e['name']); if(!empty($e['type'])&&!$ent->type){$ent->type=$e['type'];$ent->save();} $map[$e['name']]=$ent->id; }
        foreach(($out['triples']??[]) as $t){
            $sid=$map[$t['subject']]??Entity::firstOrCreate(['name'=>$t['subject']],['type'=>'Concept'])->id;
            $oid=$map[$t['object']]??Entity::firstOrCreate(['name'=>$t['object']],['type'=>'Concept'])->id;
            Triple::firstOrCreate(['subject_id'=>$sid,'predicate'=>$t['predicate'],'object_id'=>$oid],[ 'evidence'=>['source'=>$t['source']??'rule'] ]);
        }
    }
}
