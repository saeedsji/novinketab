<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Morilog\Jalali\Jalalian;

class CategoriesExport implements FromCollection, WithHeadings, WithMapping
{
    protected Collection $categories;

    public function __construct(Collection $categories)
    {
        $this->categories = $categories;
    }

    /**
     * Prepares the hierarchical collection for export.
     */
    public function collection(): Collection
    {
        return $this->buildTree($this->categories);
    }

    /**
     * Recursively builds a flat collection representing the category tree.
     */
    protected function buildTree(Collection $categories, $parentId = null, $prefix = ''): Collection
    {
        $result = new Collection();
        $filtered = $categories->where('parent_id', $parentId);

        foreach ($filtered as $category) {
            // Clone the object to avoid modifying the original collection
            $categoryClone = clone $category;
            $categoryClone->name = $prefix . ' ' . $category->name;
            $result->push($categoryClone);

            // Recursively add children
            $children = $this->buildTree($categories, $category->id, $prefix . '—');
            $result = $result->concat($children);
        }

        return $result;
    }

    public function headings(): array
    {
        return [
            'ID',
            'نام دسته‌بندی',
            'والد',
            'تعداد کتاب‌ها',
            'تاریخ ثبت',
        ];
    }

    public function map($category): array
    {
        return [
            $category->id,
            $category->name, // This name already includes the prefix
            $category->parent->name ?? '—',
            $category->books_count,
            Jalalian::forge($category->created_at)->format('Y/m/d H:i'),
        ];
    }
}
