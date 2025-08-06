<?php

namespace App\Models;

use App\Enums\User\UserAccess;
use App\Enums\User\UserGender;
use App\Enums\User\UserStatus;
use App\Enums\User\UserType;
use App\Lib\Helper\JalaliClass;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Morilog\Jalali\Jalalian;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{
    use HasRoles;
    protected $fillable = [
        'name',
        'phone',
        'email',
        'access',
        'status',
        'type',
        'gender',
        'state_id',
        'password',
        'ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => UserStatus::class,
            'access' => UserAccess::class,
            'type' => UserType::class,
            'gender' => UserGender::class,
        ];
    }


    public function info()
    {
        return !empty($this->phone) ? $this->phone : $this->email;
    }


    public function isAdmin()
    {
        return $this->access === UserAccess::admin;
    }

    public function created_at()
    {
        return Jalalian::forge($this->created_at)->format('Y/m/d (H:i:s)');
    }

    public function updated_at()
    {
        return Jalalian::forge($this->updated_at)->format('Y/m/d (H:i:s)');
    }

    /**
     * Apply common filters to the user query.
     *
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    public function scopeApplyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn($q, $status) => $q->where('status', $status))
            ->when($filters['type'] ?? null, fn($q, $type) => $q->where('type', 'like', $type))
            ->when($filters['access'] ?? null, fn($q, $access) => $q->where('access', 'like', $access))
            ->when($filters['role'] ?? null, fn($q, $role) => $q->whereHas('roles', fn($roleQuery) => $roleQuery->where('roles.name', $role)))
            ->when($filters['dateFrom'] ?? null, fn($q, $dateFrom) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($filters['dateTo'] ?? null, fn($q, $dateTo) => $q->whereDate('created_at', '<=', $dateTo));
    }
    /**
     * Get all of the price changes made by the user.
     */
    public function priceChanges(): HasMany
    {
        return $this->hasMany(BookPrice::class);
    }
}
