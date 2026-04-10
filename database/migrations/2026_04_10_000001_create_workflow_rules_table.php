<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('trigger'); // lead_created, stage_changed, deal_closed, task_overdue
            $table->json('conditions')->nullable(); // [{"field":"stage","operator":"=","value":"closed_won"}]
            $table->json('actions');  // [{"type":"send_email","to":"assigned","subject":"...","body":"..."},{"type":"create_task","title":"..."}]
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_rules');
    }
};
