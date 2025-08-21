<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(KgSampleSeeder::class);
        $this->call(QaSampleSeeder::class);
        $this->call(WebScrapingSeeder::class);
        $this->call(GraphEntitySeeder::class);
        $this->call(OrderProcessingSeeder::class);
    }
}
