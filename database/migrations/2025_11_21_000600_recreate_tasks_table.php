<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('tasks');

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('due_date')->nullable();
            $table->string('related_type')->nullable(); // Lead, Deal, Property
            $table->unsignedBigInteger('related_id')->nullable();
            $table->foreignId('assigned_to_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['open', 'in_progress', 'done'])->default('open');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->timestamps();
            $table->index(['related_type', 'related_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
