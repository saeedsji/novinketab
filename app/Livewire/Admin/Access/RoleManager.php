<?php

namespace App\Livewire\Admin\Access;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class RoleManager extends Component
{
    use WithPagination;

    // Properties for Role Management
    public ?Role $editingRole = null;
    public string $name = '';
    public array $selectedPermissions = [];

    // Modal & Title
    public bool $showRoleModal = false;
    public string $roleModalTitle = '';

    // Sorting
    public string $sortCol = 'created_at';
    public bool $sortAsc = false;

    // Filter Properties
    public string $search = '';
    public ?string $filterDateFrom = null;
    public ?string $filterDateTo = null;

    /**
     * Validation rules for the role form.
     */
    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('roles')->ignore($this->editingRole?->id),
            ],
            'selectedPermissions' => 'nullable|array',
            'selectedPermissions.*' => 'exists:permissions,name',
        ];
    }

    /**
     * Runs when a filter property is updated.
     */
    public function updated($propertyName): void
    {
        if (in_array($propertyName, ['search', 'filterDateFrom', 'filterDateTo'])) {
            $this->resetPage();
        }
    }

    /**
     * Opens the modal to create a new role.
     */
    public function createRole(): void
    {
        $this->resetRoleForm();
        $this->roleModalTitle = 'ایجاد نقش جدید';
        $this->showRoleModal = true;
    }

    /**
     * Opens the modal to edit an existing role.
     */
    public function editRole(Role $role): void
    {
        $this->resetRoleForm();
        $this->editingRole = $role;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        $this->roleModalTitle = 'ویرایش نقش';
        $this->showRoleModal = true;
    }

    /**
     * Saves a new role or updates an existing one.
     */
    public function saveRole(): void
    {
        $validatedData = $this->validate();

        $permissionsToSync = $validatedData['selectedPermissions'] ?? [];
        unset($validatedData['selectedPermissions']);

        $role = Role::updateOrCreate(['id' => $this->editingRole?->id], $validatedData);
        $role->syncPermissions($permissionsToSync);

        $message = $this->editingRole ? 'نقش با موفقیت به‌روزرسانی شد.' : 'نقش با موفقیت ایجاد شد.';
        $this->dispatch('toast', text: $message, icon: 'success');

        $this->showRoleModal = false;
    }

    /**
     * Deletes a role after confirmation.
     */
    public function deleteRole(Role $role): void
    {
        $role->delete();
        $this->dispatch('toast', text: 'نقش با موفقیت حذف شد.', icon: 'success');
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
     * Resets all filter properties.
     */
    public function clearFilters(): void
    {
        $this->reset('search', 'filterDateFrom', 'filterDateTo');
        $this->resetPage();
    }

    /**
     * Resets the role form fields and validation.
     */
    public function resetRoleForm(): void
    {
        $this->resetValidation();
        $this->reset('editingRole', 'name', 'selectedPermissions', 'showRoleModal');
    }

    /**
     * Renders the component.
     */
    public function render()
    {
        $query = Role::with('permissions');

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }
        if ($this->filterDateFrom) {
            $query->where('created_at', '>=', $this->filterDateFrom);
        }
        if ($this->filterDateTo) {
            $query->where('created_at', '<=', $this->filterDateTo);
        }

        $roles = $query->orderBy($this->sortCol, $this->sortAsc ? 'asc' : 'desc')
            ->paginate(15);

        return view('livewire.admin.access.role-manager', [
            'roles' => $roles,
            'permissions' => Permission::all(),
        ]);
    }
}
