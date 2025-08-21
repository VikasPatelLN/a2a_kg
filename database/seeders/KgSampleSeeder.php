<?php

namespace Database\Seeders;

use App\Jobs\GraphOpsAgentJob;
use App\Models\{Document, Entity, Source, Triple};
use Illuminate\Database\Seeder;
use Throwable;

class KgSampleSeeder extends Seeder
{
    public function run(): void
    {
        $src = Source::firstOrCreate(['user_id' => 1, 'type' => 'seed', 'label' => 'Sample Dataset'], ['config' => []]);
        $doc = Document::firstOrCreate(['source_id' => $src->id, 'external_id' => 'seed://sample-ecommerce'], ['title' => 'E-commerce Overview', 'content' => 'Order Service depends on Inventory Service. Order Service depends on Payment Gateway. Payment Gateway is related to Fraud Detection. Order Service is part of Order Processing. Validate Order is part of Order Processing. Reserve Inventory is part of Order Processing. Process Payment is part of Order Processing. Arrange Shipping is part of Order Processing.', 'metadata' => ['seed' => true]]);
        $entities = [['name' => 'Order Processing', 'type' => 'Process', 'properties' => []], ['name' => 'Order Service', 'type' => 'Component', 'properties' => []], ['name' => 'Inventory Service', 'type' => 'Component', 'properties' => []], ['name' => 'Payment Gateway', 'type' => 'Component', 'properties' => []], ['name' => 'Shipping Service', 'type' => 'Component', 'properties' => []], ['name' => 'Fraud Detection', 'type' => 'Component', 'properties' => []], ['name' => 'Validate Order', 'type' => 'Step', 'properties' => ['order' => 1]], ['name' => 'Reserve Inventory', 'type' => 'Step', 'properties' => ['order' => 2]], ['name' => 'Process Payment', 'type' => 'Step', 'properties' => ['order' => 3]], ['name' => 'Arrange Shipping', 'type' => 'Step', 'properties' => ['order' => 4]],];
        $map = [];
        foreach ($entities as $e) {
            $ent = Entity::firstOrCreate(['name' => $e['name']], ['type' => $e['type'], 'properties' => $e['properties'] ?? null]);
            $map[$e['name']] = $ent->id;
        }
        $triples = [['s' => 'Order Service', 'p' => 'depends_on', 'o' => 'Inventory Service'], ['s' => 'Order Service', 'p' => 'depends_on', 'o' => 'Payment Gateway'], ['s' => 'Order Service', 'p' => 'depends_on', 'o' => 'Shipping Service'], ['s' => 'Payment Gateway', 'p' => 'related_to', 'o' => 'Fraud Detection'], ['s' => 'Validate Order', 'p' => 'part_of', 'o' => 'Order Processing'], ['s' => 'Reserve Inventory', 'p' => 'part_of', 'o' => 'Order Processing'], ['s' => 'Process Payment', 'p' => 'part_of', 'o' => 'Order Processing'], ['s' => 'Arrange Shipping', 'p' => 'part_of', 'o' => 'Order Processing'], ['s' => 'Order Service', 'p' => 'part_of', 'o' => 'Order Processing'],];
        foreach ($triples as $t) {
            Triple::firstOrCreate(['subject_id' => $map[$t['s']], 'predicate' => $t['p'], 'object_id' => $map[$t['o']]], ['evidence' => ['doc_id' => $doc->id, 'seed' => true]]);
        }
        try {
            GraphOpsAgentJob::dispatchSync();
        } catch (Throwable $e) {
        }
    }
}
