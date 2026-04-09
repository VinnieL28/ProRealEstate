<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('settings');

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('default_currency')->default('USD');
            $table->json('pipeline_stages')->nullable();
            $table->json('lead_sources')->nullable();
            $table->json('team_phone_numbers')->nullable();
            $table->timestamps();
            $table->unique('team_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
