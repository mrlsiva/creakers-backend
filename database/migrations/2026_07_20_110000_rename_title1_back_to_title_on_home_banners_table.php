<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_banners', function (Blueprint $table) {
            $table->renameColumn('title1', 'title');
        });
    }

    public function down(): void
    {
        Schema::table('home_banners', function (Blueprint $table) {
            $table->renameColumn('title', 'title1');
        });
    }
};
