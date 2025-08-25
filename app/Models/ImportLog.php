<?php

namespace App\Models;

use App\Enums\Book\SalesPlatformEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Morilog\Jalali\Jalalian;

class ImportLog extends Model
{

    protected $fillable = [
        'user_id',
        'platform',
        'file_path',
        'status',
        'new_records',
        'updated_records',
        'failed_records',
        'details',
    ];

    protected $casts = [
        'platform' => SalesPlatformEnum::class,
        'details' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class,'import_log_id');
    }

    public function getCreatedAtAttribute($value)
    {
        return Jalalian::forge($value)->format('Y/m/d (H:i:s)');
    }
}
