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
        Schema::create('book_editor_pivot', function (Blueprint $table) {
            $table->primary(['book_id', 'editor_id']);
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            $table->foreignId('editor_id')->constrained('editors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_editor_pivot');
    }
};
