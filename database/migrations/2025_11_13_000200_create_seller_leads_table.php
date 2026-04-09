<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seller_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->string('property_address')->nullable();
            $table->string('status')->nullable();
            $table->string('campaign_name')->nullable();
            $table->string('drip_status')->default('new');
            $table->unsignedInteger('drip_step')->default(0);
            $table->string('team_assigned')->nullable();
            $table->string('lead_source')->nullable();
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_leads');
    }
};
