<?php

use App\Enums\Book\BookStatusEnum;
use App\Enums\Book\GenderSuitabilityEnum;
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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('financial_code')->unique()->comment('کد مالی');
            $table->string('title')->comment('عنوان کتاب');
            $table->string('taghche_title')->nullable()->comment('عنوان در طاقچه');

            // کلید خارجی برای جدول دسته بندی ها
            // جدول categories در مرحله بعد ایجاد خواهد شد
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');

            $table->tinyInteger('status')->default(BookStatusEnum::PRODUCED->value)->comment('وضعیت کتاب');
            $table->tinyInteger('gender_suitability')->default(GenderSuitabilityEnum::BOTH->value)->comment('مناسب کدام جنسیت');

            // هزینه ها و قیمت ها
            $table->bigInteger('print_price')->nullable()->comment('قیمت کتاب چاپی');
            $table->bigInteger('suggested_price')->nullable()->comment('قیمت پیشنهادی');

            // اطلاعات عددی کتاب
            $table->integer('track_count')->nullable()->comment('تعداد ترک صوتی');
            $table->integer('duration')->nullable()->comment('مدت  زمان');
            $table->integer('print_pages')->nullable()->comment('تعداد صفحات کتاب چاپی');
            $table->integer('breakeven_sales_count')->nullable()->comment('تعداد فروش برای رسیدن به نقطه سر به سر');

            // پلتفرم ها و قالب ها به صورت JSON ذخیره می شوند تا چند انتخاب را پشتیبانی کنند
            $table->json('sales_platforms')->nullable()->comment('پلتفرم های فروش');
            $table->json('formats')->nullable()->comment('قالب های کتاب');

            // شناسه های خارجی
            $table->string('novinketab_book_id')->nullable();
            $table->string('fidibo_book_id')->nullable();
            $table->string('taghcheh_book_id')->nullable();
            $table->string('navar_book_id')->nullable();
            $table->string('ketabrah_book_id')->nullable();

            $table->unsignedTinyInteger('max_discount')->nullable()->comment('ماکزیمم تخفیف به درصد');

            // فیلدهای متنی
            $table->text('description')->nullable()->comment('توضیحات کتاب');
            $table->json('tags')->nullable()->comment('تگ ها');
            $table->date('publish_date')->nullable()->comment('تاریخ انتشار');

            $table->tinyInteger('rate')->nullable()->unsigned()->comment('ریت کتاب');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
