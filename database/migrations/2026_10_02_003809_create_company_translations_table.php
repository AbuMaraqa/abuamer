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
        Schema::create('company_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 8);
            $table->string('name');
            $table->string('tagline')->nullable();
            $table->string('hero_title')->nullable();
            $table->text('hero_subtitle')->nullable();
            $table->text('introduction')->nullable();
            $table->text('story')->nullable();
            $table->text('vision')->nullable();
            $table->text('mission')->nullable();
            $table->string('cta_title')->nullable();
            $table->text('cta_text')->nullable();

            $table->unique(['company_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_translations');
    }
};
