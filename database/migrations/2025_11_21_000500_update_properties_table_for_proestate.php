<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (!Schema::hasColumn('properties', 'team_id')) {
                $table->foreignId('team_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('properties', 'lead_id')) {
                $table->foreignId('lead_id')->nullable()->after('team_id')->constrained('leads')->nullOnDelete();
            }
            if (!Schema::hasColumn('properties', 'active_deal_id')) {
                $table->foreignId('active_deal_id')->nullable()->after('lead_id')->constrained('deals')->nullOnDelete();
            }
            if (!Schema::hasColumn('properties', 'city')) {
                $table->string('city')->nullable()->after('address');
            }
            if (!Schema::hasColumn('properties', 'state')) {
                $table->string('state')->nullable()->after('city');
            }
            if (!Schema::hasColumn('properties', 'zip')) {
                $table->string('zip', 20)->nullable()->after('state');
            }
            if (!Schema::hasColumn('properties', 'type')) {
                $table->enum('type', ['sfr', 'multi_family', 'land', 'condo', 'other'])->default('sfr')->after('zip');
            }
            if (!Schema::hasColumn('properties', 'beds')) {
                $table->unsignedTinyInteger('beds')->nullable()->after('type');
            }
            if (!Schema::hasColumn('properties', 'baths')) {
                $table->unsignedTinyInteger('baths')->nullable()->after('beds');
            }
            if (!Schema::hasColumn('properties', 'sqft')) {
                $table->unsignedInteger('sqft')->nullable()->after('baths');
            }
            if (!Schema::hasColumn('properties', 'year_built')) {
                $table->unsignedInteger('year_built')->nullable()->after('sqft');
            }
            if (!Schema::hasColumn('properties', 'arv')) {
                $table->decimal('arv', 15, 2)->nullable()->after('year_built');
            }
            if (!Schema::hasColumn('properties', 'estimated_repairs')) {
                $table->decimal('estimated_repairs', 15, 2)->nullable()->after('arv');
            }
            if (!Schema::hasColumn('properties', 'estimated_rent')) {
                $table->decimal('estimated_rent', 15, 2)->nullable()->after('estimated_repairs');
            }
            if (!Schema::hasColumn('properties', 'acquisition_price')) {
                $table->decimal('acquisition_price', 15, 2)->nullable()->after('estimated_rent');
            }
            if (!Schema::hasColumn('properties', 'sale_price')) {
                $table->decimal('sale_price', 15, 2)->nullable()->after('acquisition_price');
            }
            if (!Schema::hasColumn('properties', 'status')) {
                $table->enum('status', [
                    'prospect',
                    'under_contract',
                    'owned',
                    'listed',
                    'sold',
                    'dead',
                ])->default('prospect')->after('sale_price');
            }
            if (!Schema::hasColumn('properties', 'notes')) {
                $table->longText('notes')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (Schema::hasColumn('properties', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('properties', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('properties', 'sale_price')) {
                $table->dropColumn('sale_price');
            }
            if (Schema::hasColumn('properties', 'acquisition_price')) {
                $table->dropColumn('acquisition_price');
            }
            if (Schema::hasColumn('properties', 'estimated_rent')) {
                $table->dropColumn('estimated_rent');
            }
            if (Schema::hasColumn('properties', 'estimated_repairs')) {
                $table->dropColumn('estimated_repairs');
            }
            if (Schema::hasColumn('properties', 'arv')) {
                $table->dropColumn('arv');
            }
            if (Schema::hasColumn('properties', 'year_built')) {
                $table->dropColumn('year_built');
            }
            if (Schema::hasColumn('properties', 'sqft')) {
                $table->dropColumn('sqft');
            }
            if (Schema::hasColumn('properties', 'baths')) {
                $table->dropColumn('baths');
            }
            if (Schema::hasColumn('properties', 'beds')) {
                $table->dropColumn('beds');
            }
            if (Schema::hasColumn('properties', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('properties', 'zip')) {
                $table->dropColumn('zip');
            }
            if (Schema::hasColumn('properties', 'state')) {
                $table->dropColumn('state');
            }
            if (Schema::hasColumn('properties', 'city')) {
                $table->dropColumn('city');
            }
            if (Schema::hasColumn('properties', 'active_deal_id')) {
                $table->dropConstrainedForeignId('active_deal_id');
            }
            if (Schema::hasColumn('properties', 'lead_id')) {
                $table->dropConstrainedForeignId('lead_id');
            }
            if (Schema::hasColumn('properties', 'team_id')) {
                $table->dropConstrainedForeignId('team_id');
            }
        });
    }
};
