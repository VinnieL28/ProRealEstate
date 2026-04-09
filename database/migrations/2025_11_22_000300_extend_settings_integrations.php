<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('twilio_sid')->nullable()->after('team_phone_numbers');
            $table->string('twilio_token')->nullable()->after('twilio_sid');
            $table->string('twilio_phone_number')->nullable()->after('twilio_token');

            $table->string('gmail_client_id')->nullable()->after('twilio_phone_number');
            $table->string('gmail_client_secret')->nullable()->after('gmail_client_id');
            $table->text('gmail_refresh_token')->nullable()->after('gmail_client_secret');

            $table->string('docusign_client_id')->nullable()->after('gmail_refresh_token');
            $table->string('docusign_secret')->nullable()->after('docusign_client_id');
            $table->text('docusign_account_id')->nullable()->after('docusign_secret');

            $table->string('file_storage_driver')->nullable()->after('docusign_account_id');
            $table->string('inbox_provider')->nullable()->after('file_storage_driver');
            $table->string('email_provider')->nullable()->after('inbox_provider');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            //
        });
    }
};
