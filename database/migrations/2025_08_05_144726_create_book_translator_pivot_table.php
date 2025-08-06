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
        Schema::create('book_translator_pivot', function (Blueprint $table) {
            $table->primary(['book_id', 'translator_id']);
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            $table->foreignId('translator_id')->constrained('translators')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_translator_pivot');
    }
};
