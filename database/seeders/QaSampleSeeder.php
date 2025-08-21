<?php

namespace Database\Seeders;

use App\Jobs\QaAgentJob;
use App\Models\Question;
use Illuminate\Database\Seeder;

class QaSampleSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            'Why is Order Service related to Payment Gateway?',
            'How process Order Processing works?',
            'Why is Inventory Service related to Order Service?',
            'How process Validate Order works?'
        ];
        foreach ($questions as $q) {
            $question = Question::firstOrCreate(['question' => $q]);
            QaAgentJob::dispatchSync($question);
        }
    }
}

