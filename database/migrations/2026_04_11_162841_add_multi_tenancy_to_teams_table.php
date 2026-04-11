<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable()->after('name');
            $table->string('logo')->nullable()->after('slug');
            $table->string('subscription_plan')->nullable()->default('trial')->after('logo');
            $table->timestamp('trial_ends_at')->nullable()->after('subscription_plan');
            $table->boolean('is_active')->default(true)->after('trial_ends_at');
            $table->json('onboarding_steps')->nullable()->after('is_active');
            // Stripe Cashier columns (used in Feature 2)
            $table->string('stripe_id')->nullable()->index()->after('onboarding_steps');
            $table->string('pm_type')->nullable()->after('stripe_id');
            $table->string('pm_last_four', 4)->nullable()->after('pm_type');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn([
                'slug', 'logo', 'subscription_plan', 'trial_ends_at',
                'is_active', 'onboarding_steps', 'stripe_id', 'pm_type', 'pm_last_four',
            ]);
        });
    }
};
