<?php

namespace App\Livewire\Admin\Category;

use Livewire\Component;

use App\Models\Category;
use Illuminate\Validation\Rule;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Collection;

class CategoryManager extends Component
{
    use WithPagination;

    // Properties for Category Management
    public ?Category $editingCategory = null;
    public string $name = '';
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

    /**
     * Validation rules for saving a category.
     */
    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
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

    public function sortBy($column): void
    {
        if ($this->sortCol === $column) {
            $this->sortAsc = !$this->sortAsc;
        } else {
            $this->sortAsc = true;
        }
        $this->sortCol = $column;
    }

    /**
     * ** NEW: Recursively formats categories for a dropdown select. **
     */
    private function formatCategoriesForSelect(Collection $categories, $parentId = null, $prefix = ''): array
    {
        $result = [];
        foreach ($categories->where('parent_id', $parentId) as $category) {
            $result[] = (object)['id' => $category->id, 'name' => $prefix . ' ' . $category->name];
            // Recursively call for children and merge
            $result = array_merge($result, $this->formatCategoriesForSelect($categories, $category->id, $prefix . '—'));
        }
        return $result;
    }

    /**
     * ** NEW: Recursively get all descendant IDs for a category. **
     */
    private function getDescendantIds(Category $category): array
    {
        $descendantIds = [];
        // Eager load children to avoid N+1 problems in recursion
        $children = $category->children()->with('children')->get();
        foreach ($children as $child) {
            $descendantIds[] = $child->id;
            $descendantIds = array_merge($descendantIds, $this->getDescendantIds($child));
        }
        return $descendantIds;
    }

    public function render()
    {
        $query = Category::query()->withCount('books');

        if ($this->search) {
            $query->with('parent')
                ->where('name', 'like', '%' . $this->search . '%')
                ->orWhereHas('parent', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));
        } else {
            $query->with(['children' => fn($q) => $q->withCount('books')])->whereNull('parent_id');
        }

        $categories = $query
            ->orderBy($this->sortCol, $this->sortAsc ? 'asc' : 'desc')
            ->paginate(10);

        // ** UPDATED: Prepare category tree for the modal dropdown **
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
