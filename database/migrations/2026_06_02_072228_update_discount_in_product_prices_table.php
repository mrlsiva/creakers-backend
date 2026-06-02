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
        Schema::table('product_prices', function (Blueprint $table) {
            $table->string('discount_type', 20)->default('percentage')->after('mrp');
            $table->renameColumn('discount_percent', 'discount_value');
        });
    }

    public function down(): void
    {
        Schema::table('product_prices', function (Blueprint $table) {
            $table->dropColumn('discount_type');
            $table->renameColumn('discount_value', 'discount_percent');
        });
    }
};
