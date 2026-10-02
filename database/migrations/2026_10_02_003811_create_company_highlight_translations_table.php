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
        Schema::create('company_highlight_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_highlight_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 8);
            $table->string('title');
            $table->text('description')->nullable();

            // Named explicitly: the generated name exceeds MySQL's 64-character limit.
            $table->unique(['company_highlight_id', 'locale'], 'highlight_translations_highlight_locale_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_highlight_translations');
    }
};
