<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('team_id');
            $table->string('company_logo')->nullable()->after('company_name');
            $table->string('timezone')->nullable()->default('UTC')->after('company_logo');
            $table->string('smtp_host')->nullable()->after('timezone');
            $table->unsignedSmallInteger('smtp_port')->nullable()->default(587)->after('smtp_host');
            $table->string('smtp_username')->nullable()->after('smtp_port');
            $table->string('smtp_password')->nullable()->after('smtp_username');
            $table->string('smtp_encryption')->nullable()->default('tls')->after('smtp_password');
            $table->string('smtp_from_address')->nullable()->after('smtp_encryption');
            $table->string('smtp_from_name')->nullable()->after('smtp_from_address');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'company_name', 'company_logo', 'timezone',
                'smtp_host', 'smtp_port', 'smtp_username',
                'smtp_password', 'smtp_encryption', 'smtp_from_address', 'smtp_from_name',
            ]);
        });
    }
};
