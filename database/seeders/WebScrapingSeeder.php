<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Entity;
use App\Models\Triple;

class WebScrapingSeeder extends Seeder
{
    public function run()
    {
        $tool = Entity::create(['type' => 'Tool', 'name' => 'Firecrawl Extract']);
        $feature1 = Entity::create(['type' => 'Feature', 'name' => 'Natural Language Prompt']);
        $feature2 = Entity::create(['type' => 'Feature', 'name' => 'Structured JSON Output']);
        $usecase1 = Entity::create(['type' => 'UseCase', 'name' => 'CRM Lead Enrichment']);
        $usecase2 = Entity::create(['type' => 'UseCase', 'name' => 'Competitor Monitoring']);
        $limitation = Entity::create(['type' => 'Limitation', 'name' => 'Scale Constraints']);
        $integration = Entity::create(['type' => 'Integration', 'name' => 'Zapier']);

        Triple::create(['subject_id' => $tool->id, 'predicate' => 'supports', 'object_id' => $feature1->id, 'evidence' => ['seed' => true]]);
        Triple::create(['subject_id' => $tool->id, 'predicate' => 'outputs', 'object_id' => $feature2->id, 'evidence' => ['seed' => true]]);
        Triple::create(['subject_id' => $tool->id, 'predicate' => 'enables', 'object_id' => $usecase1->id, 'evidence' => ['seed' => true]]);
        Triple::create(['subject_id' => $tool->id, 'predicate' => 'enables', 'object_id' => $usecase2->id, 'evidence' => ['seed' => true]]);
        Triple::create(['subject_id' => $tool->id, 'predicate' => 'has_limitation', 'object_id' => $limitation->id, 'evidence' => ['seed' => true]]);
        Triple::create(['subject_id' => $tool->id, 'predicate' => 'integrates_with', 'object_id' => $integration->id, 'evidence' => ['seed' => true]]);
    }
}
