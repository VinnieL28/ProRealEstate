<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('type')->nullable(); // note, call, sms, status_change, task_update, file, email
            $table->text('description')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['related_type', 'related_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
