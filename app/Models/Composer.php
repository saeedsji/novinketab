<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Composer extends Model
{
    protected $fillable = ['name', 'description'];

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'book_composer_pivot');
    }
}
