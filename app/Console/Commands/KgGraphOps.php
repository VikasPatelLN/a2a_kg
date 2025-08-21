<?php

namespace App\Console\Commands;

use App\Jobs\GraphOpsAgentJob;
use Illuminate\Console\Command;

class KgGraphOps extends Command
{
    protected $signature = 'kg:graphops {--rebuild}';
    protected $description = 'Rebuild in-DB graph artifacts: entity_stats, graph_edges, partof_closure';

    public function handle()
    {
        GraphOpsAgentJob::dispatchSync((bool)$this->option('rebuild'));
        $this->info('GraphOps rebuild complete.');
        return 0;
    }
}
