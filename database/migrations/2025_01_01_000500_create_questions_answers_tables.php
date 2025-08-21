<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->default(1);
            $t->longText('question');
            $t->json('analysis')->nullable();
            $t->timestamps();
        });
        Schema::create('answers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('question_id')->constrained()->cascadeOnDelete();
            $t->longText('answer');
            $t->json('support')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('answers');
        Schema::dropIfExists('questions');
    }
};
