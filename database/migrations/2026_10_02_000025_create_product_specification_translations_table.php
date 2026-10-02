<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Index names are explicit because the generated ones exceed MySQL's 64-character limit.
     */
    public function up(): void
    {
        Schema::create('product_specification_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_specification_id')
                ->constrained(indexName: 'spec_translations_specification_id_foreign')
                ->cascadeOnDelete();
            $table->string('locale', 8);
            $table->string('label');
            $table->string('value');

            $table->unique(['product_specification_id', 'locale'], 'spec_translations_specification_locale_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_specification_translations');
    }
};
