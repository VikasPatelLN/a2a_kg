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
            'How is Order Service related to Payment Gateway?',
            'How process Order Processing works?',
            'Why is Inventory Service related to Order Service?',
            'How process Validate Order works?',
            'Which features are supported by Firecrawl Extract?',
            'What use cases does Firecrawl Extract enable?',
            'Does Firecrawl Extract have any limitations?',
            'Which integrations are available for Firecrawl Extract?',
            'What tools output structured JSON data?',
            'What entities are related to CRM Lead Enrichment?',
            'Is Zapier integrated with any scraping tools?',
            'What are the predicates associated with Firecrawl Extract?',
            'Can you list all entities of type \'Feature\'?',
            'Which entities depend on Scale Constraints?',
            'What components does the \'Order Processing\' process depend on?',
            'Is \'Payment Gateway\' used in any processes?',
            'What are the dependencies of \'Order Processing\'?',
            'What predicates are associated with \'Order Processing\'?',
            'Can you list all triples where \'Payment Gateway\' is the object?',
            'What components does the \'User Registration\' process require?',
            'Which services use the \'Database\' component?',
            'What does the \'Refund Handling\' process depend on?',
            'Which component does \'Authentication Service\' delegate to?',
            'Are there any components used by multiple processes?'
        ];

        foreach ($questions as $q) {
            $question = Question::firstOrCreate(['question' => $q]);
            QaAgentJob::dispatchSync($question);
        }
    }
}

