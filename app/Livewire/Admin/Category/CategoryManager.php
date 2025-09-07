<?php

namespace App\Livewire\Admin\Category;

use App\Exports\CategoriesExport;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class CategoryManager extends Component
{
    use WithPagination;

    // Properties for Category Management
    public ?Category $editingCategory = null;
    public ?string $name = null;
    public ?int $parent_id = null;

    // Modal & Title
    public bool $showModal = false;
    public string $modalTitle = '';

    // Search & Sorting
    public string $search = '';
    public string $sortCol = 'created_at';
    public bool $sortAsc = false;

    // For Tree View
    public array $expanded = [];

    protected function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'min:3', 'max:255',
                Rule::unique('categories')->where(function ($query) {
                    return $query->where('parent_id', $this->parent_id);
                })->ignore($this->editingCategory?->id),
            ],
            'parent_id' => 'nullable|exists:categories,id',
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function resetForm(): void
    {
        $this->resetValidation();
        $this->reset('editingCategory', 'name', 'parent_id');
    }

    public function toggleExpand($categoryId): void
    {
        if (in_array($categoryId, $this->expanded)) {
            $this->expanded = array_diff($this->expanded, [$categoryId]);
        } else {
            $this->expanded[] = $categoryId;
        }
    }

    public function create(): void
    {
        $this->resetForm();
        $this->modalTitle = 'ایجاد دسته‌بندی جدید';
        $this->showModal = true;
    }

    public function edit(Category $category): void
    {
        $this->resetForm();
        $this->editingCategory = $category;
        $this->name = $category->name;
        $this->parent_id = $category->parent_id;
        $this->modalTitle = 'ویرایش دسته‌بندی';
        $this->showModal = true;
    }

    public function save(): void
    {
        if ($this->editingCategory && $this->editingCategory->id === $this->parent_id) {
            $this->addError('parent_id', 'یک دسته‌بندی نمی‌تواند والد خودش باشد.');
            return;
        }
        $validatedData = $this->validate();
        Category::updateOrCreate(['id' => $this->editingCategory?->id], $validatedData);
        $message = $this->editingCategory ? 'دسته‌بندی با موفقیت به‌روزرسانی شد.' : 'دسته‌بندی با موفقیت ایجاد شد.';
        $this->dispatch('toast', text: $message, icon: 'success');
        $this->showModal = false;
    }

    public function delete(Category $category): void
    {
        if ($category->children()->exists()) {
            $this->dispatch('toast', text: 'امکان حذف وجود ندارد. این دسته‌بندی دارای زیردسته است.', icon: 'error');
            return;
        }
        if ($category->books()->exists()) {
            $this->dispatch('toast', text: 'امکان حذف وجود ندارد. کتاب‌هایی به این دسته‌بندی اختصاص دارند.', icon: 'error');
            return;
        }
        $category->delete();
        $this->dispatch('toast', text: 'دسته‌بندی با موفقیت حذف شد.', icon: 'success');
    }

    public function sortBy(string $column): void
    {
        if ($this->sortCol === $column) {
            $this->sortAsc = !$this->sortAsc;
        } else {
            $this->sortAsc = true;
        }
        $this->sortCol = $column;
    }

    private function formatCategoriesForSelect(Collection $categories, $parentId = null, $prefix = ''): array
    {
        $result = [];
        foreach ($categories->where('parent_id', $parentId) as $category) {
            $result[] = (object)['id' => $category->id, 'name' => $prefix . ' ' . $category->name];
            $result = array_merge($result, $this->formatCategoriesForSelect($categories, $category->id, $prefix . '—'));
        }
        return $result;
    }

    private function getDescendantIds(Category $category): array
    {
        $descendantIds = [];
        $children = $category->children()->with('children')->get();
        foreach ($children as $child) {
            $descendantIds[] = $child->id;
            $descendantIds = array_merge($descendantIds, $this->getDescendantIds($child));
        }
        return $descendantIds;
    }

    /**
     * Exports the hierarchical category data to an Excel file.
     */
    public function exportExcel()
    {
        // Fetch all categories to build the complete tree for export
        $allCategories = Category::with('parent')->withCount('books')->get();

        return Excel::download(
            new CategoriesExport($allCategories),
            'categories-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function render()
    {
        $query = Category::query()->withCount('books');

        if ($this->search) {
            $query->with('parent')
                ->where('name', 'like', '%' . $this->search . '%')
                ->orWhereHas('parent', fn(Builder $q) => $q->where('name', 'like', '%' . $this->search . '%'));
        } else {
            // Eager load children with their book counts for the tree view
            $query->with(['children' => fn($q) => $q->withCount('books')->with('parent')])
                ->whereNull('parent_id');
        }

        $categories = $query
            ->orderBy($this->sortCol, $this->sortAsc ? 'asc' : 'desc')
            ->paginate(10);

        // Prepare category tree for the modal dropdown, excluding the current category and its descendants
        $allCategoriesQuery = Category::query();
        if ($this->editingCategory) {
            $excludeIds = array_merge([$this->editingCategory->id], $this->getDescendantIds($this->editingCategory));
            $allCategoriesQuery->whereNotIn('id', $excludeIds);
        }
        $categoryTree = $this->formatCategoriesForSelect($allCategoriesQuery->get());

        return view('livewire.admin.category.category-manager', [
            'categories' => $categories,
            'categoryTree' => $categoryTree,
        ]);
    }
}
