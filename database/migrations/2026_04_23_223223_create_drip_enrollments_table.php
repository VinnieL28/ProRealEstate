<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('drip_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('drip_campaigns')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->unsignedSmallInteger('current_step')->default(0);
            $table->enum('status', ['active', 'paused', 'completed', 'unsubscribed'])->default('active');
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamp('next_send_at')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'lead_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drip_enrollments');
    }
};
