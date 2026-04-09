<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('investment');
            $table->string('seller_name')->nullable();
            $table->string('property_address')->nullable();
            $table->string('status')->nullable();
            $table->string('deal_stage')->nullable();
            $table->decimal('list_cost', 14, 2)->nullable();
            $table->decimal('skiptrace_cost', 14, 2)->nullable();
            $table->decimal('gross_revenue', 14, 2)->nullable();
            $table->decimal('net_revenue', 14, 2)->nullable();
            $table->timestamp('social_media_posted_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
