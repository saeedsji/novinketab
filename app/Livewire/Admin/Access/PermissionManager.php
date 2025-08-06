<?php

namespace App\Livewire\Admin\Access;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;

class PermissionManager extends Component
{
    use WithPagination;

    // Properties for Permission Management
    public ?Permission $editingPermission = null;
    public string $name = '';

    // Modal & Title
    public bool $showPermissionModal = false;
    public string $permissionModalTitle = '';

    // Sorting
    public string $sortCol = 'created_at';
    public bool $sortAsc = false;

    // Filter Properties
    public string $search = '';
    public ?string $filterDateFrom = null;
    public ?string $filterDateTo = null;

    /**
     * Validation rules for the permission form.
     * The rule for 'name' is dynamic to handle unique constraints during updates.
     */
    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('permissions')->ignore($this->editingPermission?->id),
            ],
        ];
    }

    /**
     * Runs when any of the specified properties are updated.
     * Resets pagination if a filter is changed.
     */
    public function updated($propertyName): void
    {
        if (in_array($propertyName, ['search', 'filterDateFrom', 'filterDateTo'])) {
            $this->resetPage();
        }
    }

    /**
     * Resets the form and opens the modal to create a new permission.
     */
    public function createPermission(): void
    {
        $this->resetPermissionForm();
        $this->permissionModalTitle = 'ایجاد دسترسی جدید';
        $this->showPermissionModal = true;
    }

    /**
     * Populates the form and opens the modal to edit an existing permission.
     */
    public function editPermission(Permission $permission): void
    {
        $this->resetPermissionForm();
        $this->editingPermission = $permission;
        $this->name = $permission->name;
        $this->permissionModalTitle = 'ویرایش دسترسی';
        $this->showPermissionModal = true;
    }

    /**
     * Saves a new permission or updates an existing one.
     */
    public function savePermission(): void
    {
        $validatedData = $this->validate($this->rules());

        Permission::updateOrCreate(['id' => $this->editingPermission?->id], $validatedData);

        $message = $this->editingPermission ? 'دسترسی با موفقیت به‌روزرسانی شد.' : 'دسترسی با موفقیت ایجاد شد.';
        $this->dispatch('toast', text: $message, icon: 'success');

        $this->showPermissionModal = false;
    }

    /**
     * Deletes a permission after confirmation.
     */
    public function deletePermission(Permission $permission): void
    {
        $permission->delete();
        $this->dispatch('toast', text: 'دسترسی با موفقیت حذف شد.', icon: 'success');
    }

    /**
     * Sets the sorting column and direction.
     */
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
     * Gathers all active filters into an array for the query.
     */
    private function getActiveFilters(): array
    {
        return [
            'search' => $this->search,
            'dateFrom' => $this->filterDateFrom,
            'dateTo' => $this->filterDateTo,
        ];
    }

    /**
     * Resets all filter properties.
     */
    public function clearFilters(): void
    {
        $this->reset('search', 'filterDateFrom', 'filterDateTo');
        $this->resetPage();
    }

    /**
     * Resets the permission form fields and validation state.
     */
    public function resetPermissionForm(): void
    {
        $this->resetValidation();
        $this->reset('editingPermission', 'name', 'showPermissionModal');
    }

    /**
     * Renders the component.
     * Fetches permissions with applied filters and sorting.
     */
    public function render()
    {
        $query = Permission::query();

        // Apply filters directly in the render method
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }
        if ($this->filterDateFrom) {
            $query->where('created_at', '>=', $this->filterDateFrom);
        }
        if ($this->filterDateTo) {
            $query->where('created_at', '<=', $this->filterDateTo);
        }

        $permissions = $query->orderBy($this->sortCol, $this->sortAsc ? 'asc' : 'desc')
            ->paginate(15);

        return view('livewire.admin.access.permission-manager', [
            'permissions' => $permissions,
        ]);
    }
}

