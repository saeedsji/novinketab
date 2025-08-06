<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Narrator extends Model
{
    protected $fillable = ['name', 'description'];

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'book_narrator_pivot');
    }
}
