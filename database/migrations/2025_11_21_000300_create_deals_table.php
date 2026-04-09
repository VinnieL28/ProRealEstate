<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->string('name');
            $table->enum('contract_type', ['assignment', 'double_close', 'wholetail'])->default('assignment');
            $table->date('contract_date')->nullable();
            $table->date('closing_date')->nullable();
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->decimal('assignment_fee', 15, 2)->nullable();
            $table->decimal('sale_price', 15, 2)->nullable();
            $table->decimal('closing_costs', 15, 2)->nullable();
            $table->decimal('marketing_costs', 15, 2)->nullable();
            $table->decimal('profit', 15, 2)->nullable();
            $table->decimal('roi', 8, 2)->nullable();
            $table->enum('stage', [
                'analyzing',
                'contract_sent',
                'under_contract',
                'clear_to_close',
                'closed_won',
                'closed_lost',
            ])->default('analyzing');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
