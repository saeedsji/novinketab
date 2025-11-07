<?php

namespace App\Models;

use App\Enums\Book\SalesPlatformEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Morilog\Jalali\Jalalian;

class Campaign extends Model
{
    /**
     * فیلدهایی که قابلیت پر شدن به صورت انبوه را دارند.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'discount_percent',
        'platform',
    ];

    /**
     * کست کردن اتریبیوت‌ها به نوع‌های مشخص.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'discount_percent' => 'integer',
        'platform' => SalesPlatformEnum::class,
    ];

    /**
     * رابطه چند به چند با مدل کتاب.
     * هر کمپین می تواند چندین کتاب داشته باشد.
     */
    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'book_campaign_pivot');
    }

    // --- Accessors for Jalali Dates ---

    public function start_date_jalali(): ?string
    {
        if (empty($this->start_date)) {
            return null;
        }
        return Jalalian::forge($this->start_date)->format('Y/m/d');
    }

    public function end_date_jalali(): ?string
    {
        if (empty($this->end_date)) {
            return null;
        }
        return Jalalian::forge($this->end_date)->format('Y/m/d');
    }

    public function created_at_jalali()
    {
        return Jalalian::forge($this->created_at)->format('Y/m/d (H:i:s)');
    }
}
