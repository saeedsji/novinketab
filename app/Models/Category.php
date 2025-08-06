<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'parent_id'];

    // رابطه برای دسترسی به دسته بندی والد
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // رابطه برای دسترسی به زیردسته ها
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // رابطه برای دسترسی به کتاب های این دسته
    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }
}
