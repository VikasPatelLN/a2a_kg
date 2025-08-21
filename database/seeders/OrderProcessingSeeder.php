<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Entity;
use App\Models\Triple;
use App\Jobs\GraphOpsAgentJob;

class OrderProcessingSeeder extends Seeder
{
    public function run()
    {
        $a = Entity::create(['type' => 'Process', 'name' => 'Order Processing']);
        $b = Entity::create(['type' => 'Component', 'name' => 'Payment Gateway']);

        Triple::create([
            'subject_id' => $a->id,
            'predicate' => 'depends_on',
            'object_id' => $b->id,
            'evidence' => ['seed' => true]
        ]);

        GraphOpsAgentJob::dispatchSync();
    }
}
