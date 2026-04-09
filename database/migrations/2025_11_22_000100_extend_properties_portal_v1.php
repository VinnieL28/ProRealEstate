<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $add = function (string $column, callable $callback): void {
                if (!Schema::hasColumn('properties', $column)) {
                    $callback();
                }
            };

            $add('owner_name', fn () => $table->string('owner_name')->nullable()->after('lead_id'));
            $add('owner_mailing_address', fn () => $table->string('owner_mailing_address')->nullable()->after('owner_name'));

            $add('estimated_value', fn () => $table->decimal('estimated_value', 15, 2)->nullable()->after('arv'));
            $add('estimated_total_liens', fn () => $table->decimal('estimated_total_liens', 15, 2)->nullable()->after('estimated_value'));
            $add('estimated_equity', fn () => $table->decimal('estimated_equity', 15, 2)->nullable()->after('estimated_total_liens'));

            $add('lot_size', fn () => $table->unsignedInteger('lot_size')->nullable()->after('sqft'));
            $add('basement_type', fn () => $table->string('basement_type')->nullable()->after('lot_size'));
            $add('basement_area', fn () => $table->unsignedInteger('basement_area')->nullable()->after('basement_type'));
            $add('garage_type', fn () => $table->string('garage_type')->nullable()->after('basement_area'));
            $add('garage_area', fn () => $table->unsignedInteger('garage_area')->nullable()->after('garage_type'));

            $add('total_assessed_value', fn () => $table->decimal('total_assessed_value', 15, 2)->nullable()->after('property_taxes'));
            $add('assessed_land_value', fn () => $table->decimal('assessed_land_value', 15, 2)->nullable()->after('total_assessed_value'));
            $add('assessed_improvement_value', fn () => $table->decimal('assessed_improvement_value', 15, 2)->nullable()->after('assessed_land_value'));
            $add('assessed_year', fn () => $table->unsignedSmallInteger('assessed_year')->nullable()->after('assessed_improvement_value'));
            $add('tax_year', fn () => $table->unsignedSmallInteger('tax_year')->nullable()->after('assessed_year'));
            $add('property_taxes', fn () => $table->decimal('property_taxes', 15, 2)->nullable()->after('tax_year'));

            $add('mortgage_amount', fn () => $table->decimal('mortgage_amount', 15, 2)->nullable()->after('property_taxes'));
            $add('mortgage_type', fn () => $table->string('mortgage_type')->nullable()->after('mortgage_amount'));
            $add('interest_rate', fn () => $table->decimal('interest_rate', 6, 3)->nullable()->after('mortgage_type'));
            $add('mortgage_term', fn () => $table->unsignedSmallInteger('mortgage_term')->nullable()->after('interest_rate'));
            $add('original_loan_date', fn () => $table->date('original_loan_date')->nullable()->after('mortgage_term'));
            $add('mortgage_maturity_date', fn () => $table->date('mortgage_maturity_date')->nullable()->after('original_loan_date'));
            $add('lender_name', fn () => $table->string('lender_name')->nullable()->after('mortgage_maturity_date'));
            $add('current_loan_balance', fn () => $table->decimal('current_loan_balance', 15, 2)->nullable()->after('lender_name'));

            $add('last_sale_date', fn () => $table->date('last_sale_date')->nullable()->after('current_loan_balance'));
            $add('ownership_duration_months', fn () => $table->unsignedSmallInteger('ownership_duration_months')->nullable()->after('last_sale_date'));
            $add('last_sale_amount', fn () => $table->decimal('last_sale_amount', 15, 2)->nullable()->after('ownership_duration_months'));

            $add('mls_status', fn () => $table->string('mls_status')->nullable()->after('last_sale_amount'));
            $add('mls_listing_date', fn () => $table->date('mls_listing_date')->nullable()->after('mls_status'));
            $add('mls_price', fn () => $table->decimal('mls_price', 15, 2)->nullable()->after('mls_listing_date'));
            $add('mls_listing_type', fn () => $table->string('mls_listing_type')->nullable()->after('mls_price'));
            $add('mls_days_on_market', fn () => $table->unsignedInteger('mls_days_on_market')->nullable()->after('mls_listing_type'));
            $add('agent_name', fn () => $table->string('agent_name')->nullable()->after('mls_days_on_market'));
            $add('agent_phone', fn () => $table->string('agent_phone')->nullable()->after('agent_name'));
            $add('agent_email', fn () => $table->string('agent_email')->nullable()->after('agent_phone'));

            $add('owner_financing', fn () => $table->boolean('owner_financing')->nullable()->after('agent_email'));
            $add('ownership_duration_years', fn () => $table->unsignedSmallInteger('ownership_duration_years')->nullable()->after('owner_financing'));
            $add('unit_count', fn () => $table->unsignedSmallInteger('unit_count')->nullable()->after('ownership_duration_years'));
            $add('annual_taxes', fn () => $table->decimal('annual_taxes', 15, 2)->nullable()->after('unit_count'));
            $add('annual_insurance', fn () => $table->decimal('annual_insurance', 15, 2)->nullable()->after('annual_taxes'));
            $add('utilities_metered', fn () => $table->string('utilities_metered')->nullable()->after('annual_insurance'));
            $add('utilities_payer', fn () => $table->string('utilities_payer')->nullable()->after('utilities_metered'));
            $add('deferred_maintenance', fn () => $table->boolean('deferred_maintenance')->nullable()->after('utilities_payer'));
            $add('lease_type', fn () => $table->string('lease_type')->nullable()->after('deferred_maintenance'));
            $add('unit_mix', fn () => $table->text('unit_mix')->nullable()->after('lease_type'));
            $add('debt_owed', fn () => $table->decimal('debt_owed', 15, 2)->nullable()->after('unit_mix'));
            $add('vacancies', fn () => $table->unsignedSmallInteger('vacancies')->nullable()->after('debt_owed'));
            $add('property_manager_name', fn () => $table->string('property_manager_name')->nullable()->after('vacancies'));
            $add('lease_start_date', fn () => $table->date('lease_start_date')->nullable()->after('property_manager_name'));
            $add('lease_end_date', fn () => $table->date('lease_end_date')->nullable()->after('lease_start_date'));
            $add('project_type', fn () => $table->string('project_type')->nullable()->after('lease_end_date'));
            $add('sold_date', fn () => $table->date('sold_date')->nullable()->after('project_type'));
            $add('holding_period_days', fn () => $table->unsignedSmallInteger('holding_period_days')->nullable()->after('sold_date'));
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Columns are preserved on down to avoid data loss.
        });
    }
};
