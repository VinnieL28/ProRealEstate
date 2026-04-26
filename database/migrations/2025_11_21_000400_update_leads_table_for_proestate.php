<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'team_id')) {
                $table->foreignId('team_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }

            if (!Schema::hasColumn('leads', 'owner_name')) {
                $table->string('owner_name')->nullable()->after('team_id');
            }

            if (!Schema::hasColumn('leads', 'lead_source')) {
                $table->string('lead_source')->nullable()->after('email');
            }

            if (!Schema::hasColumn('leads', 'motivation_level')) {
                $table->unsignedTinyInteger('motivation_level')->nullable()->after('lead_source');
            }

            if (!Schema::hasColumn('leads', 'asking_price')) {
                $table->decimal('asking_price', 15, 2)->nullable()->after('motivation_level');
            }
            if (!Schema::hasColumn('leads', 'max_offer')) {
                $table->decimal('max_offer', 15, 2)->nullable()->after('asking_price');
            }
            if (!Schema::hasColumn('leads', 'last_offer')) {
                $table->decimal('last_offer', 15, 2)->nullable()->after('max_offer');
            }

            if (!Schema::hasColumn('leads', 'stage')) {
                $table->enum('stage', [
                    'new_lead',
                    'no_contact',
                    'contact_made',
                    'appointment_set',
                    'due_diligence',
                    'offer_made',
                    'under_contract',
                    'closed_won',
                    'closed_lost',
                ])->default('new_lead')->after('last_offer');
            }

            if (!Schema::hasColumn('leads', 'assigned_to_id')) {
                $table->foreignId('assigned_to_id')->nullable()->after('stage')->constrained('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('leads', 'active_deal_id')) {
                $table->foreignId('active_deal_id')->nullable()->after('assigned_to_id')->constrained('deals')->nullOnDelete();
            }

            if (!Schema::hasColumn('leads', 'tags')) {
                $table->json('tags')->nullable()->after('notes');
            }

            if (!Schema::hasColumn('leads', 'notes')) {
                $table->longText('notes')->nullable();
            }
        });

        if (Schema::hasColumn('leads', 'name') && Schema::hasColumn('leads', 'owner_name')) {
            DB::table('leads')->whereNull('owner_name')->update(['owner_name' => DB::raw('name')]);
        }
        if (Schema::hasColumn('leads', 'source') && Schema::hasColumn('leads', 'lead_source')) {
            DB::table('leads')->whereNull('lead_source')->update(['lead_source' => DB::raw('source')]);
        }
        if (Schema::hasColumn('leads', 'status') && Schema::hasColumn('leads', 'stage')) {
            DB::table('leads')->whereNull('stage')->update(['stage' => DB::raw('status')]);
        }

        // Split into separate Schema::table blocks for sqlite compatibility
        foreach (['name', 'source', 'status'] as $col) {
            if (Schema::hasColumn('leads', $col)) {
                Schema::table('leads', fn (Blueprint $table) => $table->dropColumn($col));
            }
        }
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'name')) {
                $table->string('name')->nullable();
            }
            if (!Schema::hasColumn('leads', 'source')) {
                $table->string('source')->nullable();
            }
            if (!Schema::hasColumn('leads', 'status')) {
                $table->string('status')->nullable();
            }

            if (Schema::hasColumn('leads', 'tags')) {
                $table->dropColumn('tags');
            }
            if (Schema::hasColumn('leads', 'active_deal_id')) {
                $table->dropConstrainedForeignId('active_deal_id');
            }
            if (Schema::hasColumn('leads', 'assigned_to_id')) {
                $table->dropConstrainedForeignId('assigned_to_id');
            }
            if (Schema::hasColumn('leads', 'stage')) {
                $table->dropColumn('stage');
            }
            if (Schema::hasColumn('leads', 'last_offer')) {
                $table->dropColumn('last_offer');
            }
            if (Schema::hasColumn('leads', 'max_offer')) {
                $table->dropColumn('max_offer');
            }
            if (Schema::hasColumn('leads', 'asking_price')) {
                $table->dropColumn('asking_price');
            }
            if (Schema::hasColumn('leads', 'motivation_level')) {
                $table->dropColumn('motivation_level');
            }
            if (Schema::hasColumn('leads', 'lead_source')) {
                $table->dropColumn('lead_source');
            }
            if (Schema::hasColumn('leads', 'owner_name')) {
                $table->dropColumn('owner_name');
            }
            if (Schema::hasColumn('leads', 'team_id')) {
                $table->dropConstrainedForeignId('team_id');
            }
        });
    }
};
