<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration{ public function up(): void { Schema::create('sources', function(Blueprint $t){ $t->id(); $t->unsignedBigInteger('user_id')->default(1); $t->string('type'); $t->string('label'); $t->json('config')->nullable(); $t->timestamps(); }); } public function down(): void { Schema::dropIfExists('sources'); } };
