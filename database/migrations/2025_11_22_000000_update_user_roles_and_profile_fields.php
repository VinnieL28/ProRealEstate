<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Profile fields
            if (!Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('users', 'middle_name')) {
                $table->string('middle_name')->nullable()->after('first_name');
            }
            if (!Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name')->nullable()->after('middle_name');
            }
            if (!Schema::hasColumn('users', 'phone_no')) {
                $table->string('phone_no')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'telephone_no')) {
                $table->string('telephone_no')->nullable()->after('phone_no');
            }
            if (!Schema::hasColumn('users', 'webwork_email')) {
                $table->string('webwork_email')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'employee_status')) {
                $table->string('employee_status')->nullable()->after('webwork_email');
            }
            if (!Schema::hasColumn('users', 'emergency_contact_name')) {
                $table->string('emergency_contact_name')->nullable()->after('employee_status');
            }
            if (!Schema::hasColumn('users', 'emergency_contact_relation')) {
                $table->string('emergency_contact_relation')->nullable()->after('emergency_contact_name');
            }
            if (!Schema::hasColumn('users', 'added_by')) {
                $table->unsignedBigInteger('added_by')->nullable()->after('emergency_contact_relation');
            }
            if (!Schema::hasColumn('users', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('added_by');
            }
            if (!Schema::hasColumn('users', 'first_login_otp')) {
                $table->string('first_login_otp', 10)->nullable()->after('updated_by');
            }
            if (!Schema::hasColumn('users', 'first_login_otp_expires_at')) {
                $table->dateTime('first_login_otp_expires_at')->nullable()->after('first_login_otp');
            }
            if (!Schema::hasColumn('users', 'is_first_time_login')) {
                $table->boolean('is_first_time_login')->default(false)->after('first_login_otp_expires_at');
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status')->nullable()->after('is_first_time_login');
            }
            if (!Schema::hasColumn('users', 'street')) {
                $table->string('street')->nullable()->after('status');
            }
            if (!Schema::hasColumn('users', 'suburb')) {
                $table->string('suburb')->nullable()->after('street');
            }
            if (!Schema::hasColumn('users', 'state')) {
                $table->string('state')->nullable()->after('suburb');
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('state');
            }
        });

        // Expand roles to cover portal roles via raw SQL to avoid Doctrine enum change issues
        if (Schema::hasColumn('users', 'role')) {
            $roles = [
                'owner',
                'admin',
                'acquisition_sales_manager',
                'transaction_coordinator',
                'acquisition_manager',
                'acquisition_closer',
                'lead_manager',
                'lead_manager_verifier',
                'cold_caller',
                'dispo_manager',
                'property_manager',
                'real_estate_agent',
            ];
            // MySQL-only — sqlite (used in tests) doesn't support ENUM modify
            if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'mysql') {
                $list = "'" . implode("','", $roles) . "'";
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY role ENUM($list) DEFAULT 'owner'");
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Do not drop columns to avoid data loss in down migration for now.
        });
    }
};
