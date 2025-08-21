<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Source;
use App\Jobs\CrawlerAgentJob;

class KgCrawl extends Command
{
    protected $signature = 'kg:crawl {url} {--depth=2} {--pages=50} {--label=CLI Source}';
    protected $description = 'Crawl a start URL and extract mentions/entities/triples';

    public function handle()
    {
        $src = Source::create([
            'user_id'=>1,
            'type'=>'web',
            'label'=>$this->option('label'),
            'config'=>['start_url'=>$this->argument('url')]
        ]);
        CrawlerAgentJob::dispatchSync($src, (int)$this->option('depth'), (int)$this->option('pages'));
        $this->info('Crawl finished.');
        return 0;
    }
}
