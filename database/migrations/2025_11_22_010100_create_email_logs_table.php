<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->string('direction')->default('inbound'); // inbound/outbound
            $table->string('subject')->nullable();
            $table->string('from_address')->nullable();
            $table->string('to_address')->nullable();
            $table->text('body_preview')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
