<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('graph_edges')) {
            Schema::create('graph_edges', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('a_id');
                $table->unsignedBigInteger('b_id');
                $table->text('predicates'); // stores JSON-encoded array of predicates
                $table->integer('weight')->default(0);
                $table->timestamps();
                $table->unique(['a_id', 'b_id']);
                $table->index('a_id');
                $table->index('b_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('graph_edges');
    }
};

