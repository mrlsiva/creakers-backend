<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_contacts', function (Blueprint $table) {
            $table->string('twitter_url')->nullable()->after('youtube_url');
            $table->string('whatsapp')->nullable()->after('twitter_url');
            $table->string('telegram_url')->nullable()->after('whatsapp');
            $table->string('tiktok_url')->nullable()->after('telegram_url');
            $table->string('linkedin_url')->nullable()->after('tiktok_url');
            $table->string('pinterest_url')->nullable()->after('linkedin_url');
        });
    }

    public function down(): void
    {
        Schema::table('site_contacts', function (Blueprint $table) {
            $table->dropColumn(['twitter_url', 'whatsapp', 'telegram_url', 'tiktok_url', 'linkedin_url', 'pinterest_url']);
        });
    }
};
