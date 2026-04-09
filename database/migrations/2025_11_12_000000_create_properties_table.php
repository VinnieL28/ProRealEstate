<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('address');

            $table->unsignedSmallInteger('bedrooms')->nullable();
            $table->unsignedSmallInteger('bathrooms')->nullable();
            $table->unsignedSmallInteger('kitchens')->nullable();
            $table->unsignedInteger('sqft')->nullable();
            $table->decimal('price', 14, 2)->nullable();

            $table->string('owner_name')->nullable();
            $table->string('owner_mailing_address')->nullable();

            $table->decimal('estimated_value', 14, 2)->nullable();
            $table->decimal('estimated_total_liens', 14, 2)->nullable();
            $table->decimal('estimated_equity', 14, 2)->nullable();

            $table->string('property_type')->nullable();
            $table->unsignedInteger('lot_size')->nullable();
            $table->unsignedSmallInteger('year_built')->nullable();
            $table->string('basement_type')->nullable();
            $table->unsignedInteger('basement_area')->nullable();
            $table->string('garage_type')->nullable();
            $table->unsignedInteger('garage_area')->nullable();

            $table->decimal('total_assessed_value', 14, 2)->nullable();
            $table->decimal('assessed_land_value', 14, 2)->nullable();
            $table->decimal('assessed_improvement_value', 14, 2)->nullable();
            $table->unsignedSmallInteger('assessed_year')->nullable();
            $table->unsignedSmallInteger('tax_year')->nullable();
            $table->decimal('property_taxes', 14, 2)->nullable();

            $table->decimal('mortgage_amount', 14, 2)->nullable();
            $table->string('mortgage_type')->nullable();
            $table->decimal('interest_rate', 5, 2)->nullable();
            $table->unsignedSmallInteger('mortgage_term')->nullable();
            $table->date('original_loan_date')->nullable();
            $table->date('mortgage_maturity_date')->nullable();
            $table->string('lender_name')->nullable();
            $table->decimal('current_loan_balance', 14, 2)->nullable();

            $table->string('mls_status')->nullable();
            $table->date('mls_listing_date')->nullable();
            $table->decimal('mls_price', 14, 2)->nullable();
            $table->string('mls_listing_type')->nullable();
            $table->unsignedInteger('mls_days_on_market')->nullable();
            $table->string('agent_name')->nullable();
            $table->string('agent_phone')->nullable();
            $table->string('agent_email')->nullable();

            $table->json('raw_api')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};

