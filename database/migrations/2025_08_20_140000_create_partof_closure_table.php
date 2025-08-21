<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('partof_closure')) {
            Schema::create('partof_closure', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('ancestor_id');
                $table->unsignedBigInteger('descendant_id');
                $table->integer('depth');
                $table->timestamps();
                $table->unique(['ancestor_id', 'descendant_id', 'depth'], 'partof_closure_unique');
                $table->index('ancestor_id');
                $table->index('descendant_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('partof_closure');
    }
};

