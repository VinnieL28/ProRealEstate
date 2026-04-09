<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $add = function (string $column, callable $callback) {
                if (!Schema::hasColumn('leads', $column)) {
                    $callback();
                }
            };

            $add('first_name', fn () => $table->string('first_name')->nullable()->after('team_id'));
            $add('last_name', fn () => $table->string('last_name')->nullable()->after('first_name'));
            $add('primary_phone', fn () => $table->string('primary_phone')->nullable()->after('phone'));
            $add('primary_email', fn () => $table->string('primary_email')->nullable()->after('email'));
            $add('major_market', fn () => $table->string('major_market')->nullable()->after('lead_source'));

            $add('property_address', fn () => $table->string('property_address')->nullable()->after('major_market'));
            $add('owner_mailing_address', fn () => $table->string('owner_mailing_address')->nullable()->after('property_address'));
            $add('mortgage_on_house', fn () => $table->decimal('mortgage_on_house', 15, 2)->nullable()->after('owner_mailing_address'));

            $add('tenant_lease_type', fn () => $table->string('tenant_lease_type')->nullable()->after('unit_mix'));
            $add('property_manager_name', fn () => $table->string('property_manager_name')->nullable()->after('tenant_lease_type'));
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // columns kept on down to avoid data loss
        });
    }
};
