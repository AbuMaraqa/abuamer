<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * button_url holds a custom link per language (used when the slide links to a custom URL).
     */
    public function up(): void
    {
        Schema::create('slide_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slide_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 8);
            $table->string('eyebrow')->nullable();
            $table->string('title');
            $table->text('text')->nullable();
            $table->string('button_label')->nullable();
            $table->string('button_url', 500)->nullable();

            $table->unique(['slide_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slide_translations');
    }
};
