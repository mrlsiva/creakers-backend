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
        Schema::table('site_contents', function (Blueprint $table) {
            $table->string('tag')->nullable()->after('title');
            $table->string('image')->nullable()->after('body');
            $table->json('features')->nullable()->after('image');
            $table->string('button_label')->nullable()->after('features');
            $table->string('button_url')->nullable()->after('button_label');
            $table->boolean('button_open_in_new_tab')->default(false)->after('button_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_contents', function (Blueprint $table) {
            $table->dropColumn([
                'tag', 'image', 'features', 'button_label', 'button_url', 'button_open_in_new_tab',
            ]);
        });
    }
};
