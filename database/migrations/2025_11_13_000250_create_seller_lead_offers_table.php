<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seller_lead_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_lead_id')->constrained()->cascadeOnDelete();
            $table->string('buyer_name')->nullable();
            $table->decimal('amount', 14, 2)->nullable();
            $table->string('status')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_lead_offers');
    }
};
