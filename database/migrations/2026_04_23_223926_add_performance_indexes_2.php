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
        // Leads: hot lead queries, score filtering, stage + team compound
        Schema::table('leads', function (Blueprint $table) {
            if (!$this->indexExists('leads', 'leads_score_index')) {
                $table->index('score', 'leads_score_index');
            }
            if (!$this->indexExists('leads', 'leads_team_stage_index')) {
                $table->index(['team_id', 'stage'], 'leads_team_stage_index');
            }
            if (!$this->indexExists('leads', 'leads_assigned_to_id_index')) {
                $table->index('assigned_to_id', 'leads_assigned_to_id_index');
            }
        });

        // Deals: stage + team, esign lookups
        Schema::table('deals', function (Blueprint $table) {
            if (!$this->indexExists('deals', 'deals_team_stage_index')) {
                $table->index(['team_id', 'stage'], 'deals_team_stage_index');
            }
            if (!$this->indexExists('deals', 'deals_esign_status_index')) {
                $table->index('esign_status', 'deals_esign_status_index');
            }
        });

        // Tasks: open + overdue lookups
        Schema::table('tasks', function (Blueprint $table) {
            if (!$this->indexExists('tasks', 'tasks_team_status_index')) {
                $table->index(['team_id', 'status'], 'tasks_team_status_index');
            }
            if (!$this->indexExists('tasks', 'tasks_due_date_index')) {
                $table->index('due_date', 'tasks_due_date_index');
            }
        });

        // Activities: team feed
        Schema::table('activities', function (Blueprint $table) {
            if (!$this->indexExists('activities', 'activities_team_created_index')) {
                $table->index(['team_id', 'created_at'], 'activities_team_created_index');
            }
        });

        // Drip enrollments
        Schema::table('drip_enrollments', function (Blueprint $table) {
            if (!$this->indexExists('drip_enrollments', 'drip_enrollments_team_status_index')) {
                $table->index(['team_id', 'status'], 'drip_enrollments_team_status_index');
            }
            if (!$this->indexExists('drip_enrollments', 'drip_enrollments_next_send_at_index')) {
                $table->index('next_send_at', 'drip_enrollments_next_send_at_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex('leads_score_index');
            $table->dropIndex('leads_team_stage_index');
            $table->dropIndex('leads_assigned_to_id_index');
        });
        Schema::table('deals', function (Blueprint $table) {
            $table->dropIndex('deals_team_stage_index');
            $table->dropIndex('deals_esign_status_index');
        });
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('tasks_team_status_index');
            $table->dropIndex('tasks_due_date_index');
        });
        Schema::table('activities', function (Blueprint $table) {
            $table->dropIndex('activities_team_created_index');
        });
        Schema::table('drip_enrollments', function (Blueprint $table) {
            $table->dropIndex('drip_enrollments_team_status_index');
            $table->dropIndex('drip_enrollments_next_send_at_index');
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        // Skip on non-MySQL drivers (sqlite/pgsql) — those drivers don't support SHOW INDEX
        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() !== 'mysql') {
            return true;
        }

        return collect(\Illuminate\Support\Facades\DB::select("SHOW INDEX FROM `{$table}`"))
            ->pluck('Key_name')
            ->contains($index);
    }
};
