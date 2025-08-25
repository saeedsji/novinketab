<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_log_id')->constrained('import_logs')->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            $table->tinyInteger('sale_platform')->comment('پلتفرم');
            $table->string('platform_id')->comment('شناسه پلتفرم');

            // اطلاعات فروش و پرداخت
            $table->dateTime('sale_date')->nullable()->comment('تاریخ و زمان فروش');
            $table->integer('amount')->nullable()->comment('مبلغ نهایی فروش');
            $table->integer('publisher_share')->nullable()->comment('سهم ناشر');
            $table->integer('platform_share')->nullable()->comment('سهم کانال فروش');
            $table->integer('discount')->nullable()->comment('مبلغ تخفیف');
            $table->integer('tax')->nullable()->comment('مالیات');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
