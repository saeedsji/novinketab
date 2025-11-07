<?php

namespace App\Models;

use App\Enums\Book\BookRateEnum;
use App\Enums\Book\BookStatusEnum;
use App\Enums\Book\GenderSuitabilityEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Morilog\Jalali\Jalalian;

class Book extends Model
{

    /**
     * فیلدهایی که قابلیت پر شدن به صورت انبوه را دارند.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'financial_code',
        'title',
        'taghche_title',
        'category_id',
        'status',
        'gender_suitability',
        'print_price',
        'suggested_price',
        'track_count',
        'duration',
        'print_pages',
        'breakeven_sales_count',
        'sales_platforms',
        'formats',
        'fidibo_book_id',
        'taghcheh_book_id',
        'navar_book_id',
        'ketabrah_book_id',
        'max_discount',
        'description',
        'tags',
        'publish_date',
        'rate',
    ];

    /**
     * کست کردن اتریبیوت‌ها به نوع‌های مشخص.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => BookStatusEnum::class,
        'gender_suitability' => GenderSuitabilityEnum::class,
        'rate' => BookRateEnum::class,
        'sales_platforms' => 'array', // برای ذخیره چندین پلتفرم
        'formats' => 'array',         // برای ذخیره چندین قالب
        'tags' => 'array',
        'publish_date' => 'date',
        'estimated_cost' => 'integer',
        'print_price' => 'integer',
        'suggested_price' => 'integer',
    ];

    // --- تعریف روابط ---

    /**
     * رابطه یک به چند با مدل دسته بندی.
     * هر کتاب متعلق به یک دسته بندی است.
     */
    public function category(): BelongsTo
    {
        // فرض بر این است که مدل Category وجود دارد
        return $this->belongsTo(Category::class);
    }

    /**
     * رابطه چند به چند با مدل نویسنده.
     * هر کتاب می تواند چندین نویسنده داشته باشد.
     */
    public function authors(): BelongsToMany
    {
        // فرض بر این است که مدل Author و جدول واسط book_author وجود دارد
        return $this->belongsToMany(Author::class, 'book_author_pivot');
    }

    /**
     * رابطه چند به چند با مدل مترجم.
     */
    public function translators(): BelongsToMany
    {
        // فرض بر این است که مدل Translator و جدول واسط book_translator وجود دارد
        return $this->belongsToMany(Translator::class, 'book_translator_pivot');
    }

    /**
     * رابطه چند به چند با مدل گوینده.
     */
    public function narrators(): BelongsToMany
    {
        // فرض بر این است که مدل Narrator و جدول واسط book_narrator وجود دارد
        return $this->belongsToMany(Narrator::class, 'book_narrator_pivot');
    }

    /**
     * رابطه چند به چند با مدل آهنگساز.
     */
    public function composers(): BelongsToMany
    {
        // فرض بر این است که مدل Composer و جدول واسط book_composer وجود دارد
        return $this->belongsToMany(Composer::class, 'book_composer_pivot');
    }

    /**
     * رابطه چند به چند با مدل تدوینگر.
     */
    public function editors(): BelongsToMany
    {
        // فرض بر این است که مدل editor و جدول واسط book_editor_pivot وجود دارد
        return $this->belongsToMany(Editor::class, 'book_editor_pivot');
    }

    /**
     * رابطه چند به چند با مدل ناشر.
     */
    public function publishers(): BelongsToMany
    {
        // فرض بر این است که مدل Publisher و جدول واسط book_publisher وجود دارد
        return $this->belongsToMany(Publisher::class, 'book_publisher_pivot');
    }

    /**
     * Get all price history for the book.
     */
    public function prices(): HasMany
    {
        return $this->hasMany(BookPrice::class)->orderBy('effective_date', 'desc');
    }

    /**
     * Get the latest price for the book.
     * This relation will return the single most recent price record.
     */
    public function latestPrice(): HasOne
    {
        return $this->hasOne(BookPrice::class)->ofMany('effective_date', 'max');
    }

    public function created_at()
    {
        return Jalalian::forge($this->created_at)->format('Y/m/d (H:i:s)');
    }

    public function updated_at()
    {
        return Jalalian::forge($this->updated_at)->format('Y/m/d (H:i:s)');
    }

    public function publish_date()
    {
        if (empty($this->publish_date))
            return null;
        return Jalalian::forge($this->publish_date)->format('Y/m/d');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
