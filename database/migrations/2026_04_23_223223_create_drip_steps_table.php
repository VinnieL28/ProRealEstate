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
        Schema::create('drip_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('drip_campaigns')->cascadeOnDelete();
            $table->unsignedTinyInteger('step_order')->default(1);
            $table->enum('channel', ['email', 'sms'])->default('email');
            $table->unsignedSmallInteger('delay_days')->default(0);
            $table->string('subject')->nullable();
            $table->text('body');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drip_steps');
    }
};
