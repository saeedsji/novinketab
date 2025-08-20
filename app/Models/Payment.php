<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Morilog\Jalali\Jalalian;

class Payment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'book_id',
        'sale_platform',
        'platform_id',
        'sale_date',
        'amount',
        'publisher_share',
        'platform_share',
        'discount',
        'tax',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sale_date' => 'datetime',
        'amount' => 'integer',
        'publisher_share' => 'integer',
        'platform_share' => 'integer',
        'discount' => 'integer',
        'tax' => 'integer',
    ];

    /**
     * Get the book that owns the payment.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function created_at()
    {
        return Jalalian::forge($this->created_at)->format('Y/m/d (H:i:s)');
    }

    public function updated_at()
    {
        return Jalalian::forge($this->updated_at)->format('Y/m/d (H:i:s)');
    }

}
