<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookPrice extends Model
{
    protected $fillable = [
        'book_id',
        'user_id',
        'price',
        'effective_date',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'price' => 'integer',
    ];

    /**
     * Get the book that owns the price.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the user who set the price.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
