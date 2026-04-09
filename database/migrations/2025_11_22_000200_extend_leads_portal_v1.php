<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Motivation / occupancy
            $table->string('occupancy_status')->nullable()->after('motivation_level'); // vacant/occupied
            $table->string('sell_timeline')->nullable()->after('occupancy_status'); // asap/next few months/etc
            $table->string('sell_reason')->nullable()->after('sell_timeline'); // free text / options
            $table->boolean('listed_with_agent')->nullable()->after('sell_reason');
            $table->boolean('past_due_notice')->nullable()->after('listed_with_agent');

            // Pricing
            $table->decimal('min_cash_offer', 15, 2)->nullable()->after('last_offer');

            // Rental lead flags
            $table->boolean('open_to_owner_financing')->nullable()->after('min_cash_offer');
            $table->string('ownership_duration')->nullable()->after('open_to_owner_financing');
            $table->unsignedSmallInteger('rental_unit_count')->nullable()->after('ownership_duration');
            $table->decimal('annual_taxes', 15, 2)->nullable()->after('rental_unit_count');
            $table->decimal('annual_insurance', 15, 2)->nullable()->after('annual_taxes');
            $table->string('utilities_metered')->nullable()->after('annual_insurance');
            $table->string('utilities_payer')->nullable()->after('utilities_metered');
            $table->text('unit_mix')->nullable()->after('utilities_payer');
            $table->unsignedSmallInteger('vacancies')->nullable()->after('unit_mix');
            $table->boolean('deferred_maintenance')->nullable()->after('vacancies');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Columns are preserved on down.
        });
    }
};
