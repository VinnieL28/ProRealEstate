<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }

            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', [
                    'owner',
                    'admin',
                    'acquisition_manager',
                    'lead_manager',
                    'cold_caller',
                    'dispo_manager',
                ])->default('owner')->after('phone');
            }

            if (!Schema::hasColumn('users', 'team_id')) {
                $table->foreignId('team_id')->nullable()->after('id')->constrained('teams')->nullOnDelete();
            }

            if (!Schema::hasColumn('users', 'dark_mode_pref')) {
                $table->boolean('dark_mode_pref')->default(true)->after('remember_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'phone')) {
                $table->dropColumn('phone');
            }
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
            if (Schema::hasColumn('users', 'team_id')) {
                $table->dropConstrainedForeignId('team_id');
            }
            if (Schema::hasColumn('users', 'dark_mode_pref')) {
                $table->dropColumn('dark_mode_pref');
            }
        });
    }
};
