<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The brand is optional; deleting a brand keeps its products and clears their brand.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('category_id');

            // Serves brand pages and brand filters (active products in display order); it
            // also backs the foreign key, so it is created first.
            $table->index(['brand_id', 'status', 'sort_order']);
            $table->foreign('brand_id')->references('id')->on('brands')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropIndex(['brand_id', 'status', 'sort_order']);
            $table->dropColumn('brand_id');
        });
    }
};
