<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->morphs('taskable');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('assigned_to')->nullable();
            $table->string('priority')->default('normal');
            $table->string('status')->default('open');
            $table->timestamp('due_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
