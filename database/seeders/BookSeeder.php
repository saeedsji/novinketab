<?php

namespace Database\Seeders;

use App\Imports\BooksImport;
use App\Models\Author;
use App\Models\Book;
use App\Models\BookPrice;
use App\Models\Category;
use App\Models\Composer;
use App\Models\Narrator;
use App\Models\Publisher;
use App\Models\Translator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * php artisan db:seed --class=BookSeeder
     */
    public function run(): void
    {
        // 1. Clean up the database before seeding
        $this->command->info('Truncating tables...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Book::truncate();
        BookPrice::truncate();
        Category::truncate();
        Author::truncate();
        Translator::truncate();
        Narrator::truncate();
        Composer::truncate();
        Publisher::truncate();
        DB::table('book_author_pivot')->truncate();
        DB::table('book_translator_pivot')->truncate();
        DB::table('book_narrator_pivot')->truncate();
        DB::table('book_composer_pivot')->truncate();
        DB::table('book_publisher_pivot')->truncate();
        DB::table('book_editor_pivot')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $this->command->info('Tables truncated successfully.');

        // 2. Define the path to your Excel file
        $filePath = base_path('database/data/final-books-with-tags.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error('Excel file not found at: ' . $filePath);
            return;
        }

        // 3. Import data using the BooksImport class
        $this->command->info('Importing books from Excel file...');
        Excel::import(new BooksImport, $filePath);
        $this->command->info('Books imported successfully!');
    }
}
