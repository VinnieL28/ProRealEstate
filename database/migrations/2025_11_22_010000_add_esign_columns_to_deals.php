<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            if (!Schema::hasColumn('deals', 'esign_provider')) {
                $table->string('esign_provider')->nullable()->after('stage');
            }
            if (!Schema::hasColumn('deals', 'esign_envelope_id')) {
                $table->string('esign_envelope_id')->nullable()->after('esign_provider');
            }
            if (!Schema::hasColumn('deals', 'esign_status')) {
                $table->string('esign_status')->nullable()->after('esign_envelope_id');
            }
            if (!Schema::hasColumn('deals', 'esign_sent_at')) {
                $table->dateTime('esign_sent_at')->nullable()->after('esign_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            // preserve columns on down
        });
    }
};
