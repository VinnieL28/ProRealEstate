<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (!Schema::hasColumn('properties', 'zpid')) {
                $table->string('zpid')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (Schema::hasColumn('properties', 'zpid')) {
                $table->dropColumn('zpid');
            }
        });
    }
};

