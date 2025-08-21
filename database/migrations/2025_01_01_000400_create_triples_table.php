<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('triples', function (Blueprint $t) {
            $t->id();
            $t->foreignId('subject_id')->constrained('entities')->cascadeOnDelete();
            $t->longText('predicate');
            $t->foreignId('object_id')->constrained('entities')->cascadeOnDelete();
            $t->json('evidence')->nullable();
            $t->timestamps();
            $t->unique(['subject_id', 'object_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('triples');
    }
};
