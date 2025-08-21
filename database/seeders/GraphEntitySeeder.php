<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Entity;
use App\Models\Triple;
use App\Jobs\GraphOpsAgentJob;

class GraphEntitySeeder extends Seeder
{
    public function run()
    {
        $entities = [];

        // Create Entities
        $entities['Refund Handling'] = Entity::create(['type'=>'Process','name'=>'Refund Handling']);
        $entities['Checkout'] = Entity::create(['type'=>'Process','name'=>'Checkout']);
        $entities['User Registration'] = Entity::create(['type'=>'Process','name'=>'User Registration']);
        $entities['Inventory Management'] = Entity::create(['type'=>'Service','name'=>'Inventory Management']);
        $entities['Email Verification'] = Entity::create(['type'=>'Component','name'=>'Email Verification']);
        $entities['Database'] = Entity::create(['type'=>'Component','name'=>'Database']);
        $entities['Cart'] = Entity::create(['type'=>'Component','name'=>'Cart']);
        $entities['Payment Processor'] = Entity::create(['type'=>'Component','name'=>'Payment Processor']);
        $entities['Authentication Service'] = Entity::create(['type'=>'Component','name'=>'Authentication Service']);
        $entities['OAuth Provider'] = Entity::create(['type'=>'Component','name'=>'OAuth Provider']);

        // Create Relationships
        Triple::create(['subject_id'=>$entities['User Registration']->id,'predicate'=>'requires','object_id'=>$entities['Email Verification']->id,'evidence'=>['seed'=>true]]);
        Triple::create(['subject_id'=>$entities['Inventory Management']->id,'predicate'=>'uses','object_id'=>$entities['Database']->id,'evidence'=>['seed'=>true]]);
        Triple::create(['subject_id'=>$entities['Checkout']->id,'predicate'=>'interacts_with','object_id'=>$entities['Cart']->id,'evidence'=>['seed'=>true]]);
        Triple::create(['subject_id'=>$entities['Refund Handling']->id,'predicate'=>'depends_on','object_id'=>$entities['Payment Processor']->id,'evidence'=>['seed'=>true]]);
        Triple::create(['subject_id'=>$entities['Authentication Service']->id,'predicate'=>'delegates_to','object_id'=>$entities['OAuth Provider']->id,'evidence'=>['seed'=>true]]);

        // Dispatch graph job
        GraphOpsAgentJob::dispatchSync();
    }
}
