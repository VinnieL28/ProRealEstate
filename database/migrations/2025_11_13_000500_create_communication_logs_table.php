<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('communication_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->morphs('loggable');
            $table->dateTime('occurred_at')->nullable();
            $table->string('medium')->default('call');
            $table->string('number')->nullable();
            $table->string('number_name')->nullable();
            $table->string('direction')->nullable();
            $table->string('recording_url')->nullable();
            $table->unsignedInteger('duration_secs')->nullable();
            $table->text('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_logs');
    }
};
