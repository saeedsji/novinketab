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

            // کلید خارجی برای جدول دسته بندی ها
            // جدول categories در مرحله بعد ایجاد خواهد شد
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');

            $table->tinyInteger('status')->default(BookStatusEnum::DRAFT->value)->comment('وضعیت کتاب');

            // هزینه ها و قیمت ها
            $table->bigInteger('print_price')->nullable()->comment('قیمت کتاب چاپی');
            $table->bigInteger('suggested_price')->nullable()->comment('قیمت پیشنهادی');

            // اطلاعات عددی کتاب
            $table->integer('track_count')->nullable()->comment('تعداد ترک صوتی');
            $table->integer('print_pages')->nullable()->comment('تعداد صفحات کتاب چاپی');
            $table->integer('breakeven_sales_count')->nullable()->comment('تعداد فروش برای رسیدن به نقطه سر به سر');

            // پلتفرم ها و قالب ها به صورت JSON ذخیره می شوند تا چند انتخاب را پشتیبانی کنند
            $table->json('sales_platforms')->nullable()->comment('پلتفرم های فروش');
            $table->json('formats')->nullable()->comment('قالب های کتاب');

            $table->tinyInteger('listener_type')->nullable()->comment('نوع شنونده');

            // ریت ها
            $table->tinyInteger('author_rate',)->nullable()->comment('ریت نویسنده');
            $table->tinyInteger('narrator_rate',)->nullable()->comment('ریت گوینده');
            $table->tinyInteger('editor_composer_rate',)->nullable()->comment('ریت تدوین/آهنگساز');
            $table->tinyInteger('translator_rate')->nullable()->comment('ریت مترجم');

            // شناسه های خارجی
            $table->string('fidibo_book_id')->nullable();
            $table->string('taghcheh_book_id')->nullable();
            $table->string('navar_book_id')->nullable();
            $table->string('ketabrah_book_id')->nullable();

            $table->unsignedTinyInteger('max_discount')->nullable()->comment('ماکزیمم تخفیف به درصد');

            // فیلدهای متنی
            $table->text('description')->nullable()->comment('توضیحات کتاب');
            $table->json('tags')->nullable()->comment('تگ ها');
            $table->string('based_on')->nullable()->comment('اقتباس از');
            $table->text('awards')->nullable()->comment('جوایز');

            $table->date('publish_date')->nullable()->comment('تاریخ انتشار');
            $table->tinyInteger('gender_suitability')->default(GenderSuitabilityEnum::BOTH->value)->comment('مناسب کدام جنسیت');

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
