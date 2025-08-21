<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('entity_stats')) {
            Schema::create('entity_stats', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('entity_id');
                $table->bigInteger('degree')->default(0);
                $table->bigInteger('out_degree')->default(0);
                $table->bigInteger('in_degree')->default(0);
                $table->bigInteger('mentions')->default(0);
                $table->timestamps();
                $table->unique(['entity_id']);
                $table->index('entity_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_stats');
    }
};
