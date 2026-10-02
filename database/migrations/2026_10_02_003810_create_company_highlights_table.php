<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Short repeatable blocks of company content: values, reasons to choose the
     * company, and statistics (the "value" column holds figures such as "25+").
     */
    public function up(): void
    {
        Schema::create('company_highlights', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->string('icon', 40)->nullable();
            $table->string('value', 40)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['type', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_highlights');
    }
};
