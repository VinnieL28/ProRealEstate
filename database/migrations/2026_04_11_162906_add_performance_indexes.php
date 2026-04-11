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
        Schema::table('leads', function (Blueprint $table) {
            // Only add if not already indexed
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = array_keys($sm->listTableIndexes('leads'));
            if (!in_array('leads_team_id_index', $indexes)) {
                $table->index('team_id');
            }
            if (!in_array('leads_stage_index', $indexes)) {
                $table->index('stage');
            }
            if (!in_array('leads_score_index', $indexes)) {
                $table->index('score');
            }
            if (!in_array('leads_assigned_to_id_index', $indexes)) {
                $table->index('assigned_to_id');
            }
        });

        Schema::table('deals', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = array_keys($sm->listTableIndexes('deals'));
            if (!in_array('deals_team_id_index', $indexes)) {
                $table->index('team_id');
            }
            if (!in_array('deals_stage_index', $indexes)) {
                $table->index('stage');
            }
        });

        Schema::table('tasks', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = array_keys($sm->listTableIndexes('tasks'));
            if (!in_array('tasks_due_date_index', $indexes)) {
                $table->index('due_date');
            }
            if (!in_array('tasks_assigned_to_id_index', $indexes)) {
                $table->index('assigned_to_id');
            }
        });

        Schema::table('properties', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = array_keys($sm->listTableIndexes('properties'));
            if (!in_array('properties_team_id_index', $indexes)) {
                $table->index('team_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['team_id']);
            $table->dropIndex(['stage']);
            $table->dropIndex(['score']);
            $table->dropIndex(['assigned_to_id']);
        });
        Schema::table('deals', function (Blueprint $table) {
            $table->dropIndex(['team_id']);
            $table->dropIndex(['stage']);
        });
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['due_date']);
            $table->dropIndex(['assigned_to_id']);
        });
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex(['team_id']);
        });
    }
};
