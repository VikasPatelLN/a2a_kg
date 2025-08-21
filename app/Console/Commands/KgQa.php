<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Question;
use App\Jobs\QaAgentJob;

class KgQa extends Command
{
    protected $signature = 'kg:qa {question*}';
    protected $description = 'Ask a KG question (why/how)';

    public function handle()
    {
        $q = implode(' ', $this->argument('question'));
        $question = Question::create(['user_id'=>1,'question'=>$q]);
        QaAgentJob::dispatchSync($question);
        $this->info('Answer created. Check dashboard.');
        return 0;
    }
}
