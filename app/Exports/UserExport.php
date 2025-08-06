<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UserExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected array $filters,
        protected string $sortCol,
        protected bool $sortAsc
    ) {}

    public function query()
    {
        return User::query()
            ->applyFilters($this->filters)
            ->orderBy($this->sortCol, $this->sortAsc ? 'asc' : 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'نام کامل',
            'ایمیل',
            'شماره موبایل',
            'وضعیت',
            'نوع کاربر',
            'سطح دسترسی',
            'نقش‌ها',
            'تاریخ ثبت نام',
        ];
    }

    /** @param User $user */
    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->phone,
            $user->status->pName(),
            $user->type->pName(),
            $user->access->pName(),
            $user->getRoleNames()->implode(', '),
            $user->created_at(),
        ];
    }
}
