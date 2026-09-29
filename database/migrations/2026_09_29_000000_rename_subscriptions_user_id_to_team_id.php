<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Subscriptions belong to teams (Team uses Cashier's Billable trait), but the
 * table was first created with Cashier's default `user_id` column. Every query
 * for `subscriptions.team_id` then failed, crashing the panel right after login.
 *
 * Fresh installs now get `team_id` from the create migration; this one repairs
 * databases that were migrated before that fix.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('subscriptions', 'user_id') || Schema::hasColumn('subscriptions', 'team_id')) {
            return;
        }

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'stripe_status']);
        });
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->renameColumn('user_id', 'team_id');
        });
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->index(['team_id', 'stripe_status']);
        });
    }

    public function down(): void
    {
        // Intentionally empty: going back to user_id would re-break billing.
    }
};
