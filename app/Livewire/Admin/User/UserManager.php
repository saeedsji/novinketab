<?php

namespace App\Livewire\Admin\User;

use App\Enums\User\UserAccess;
use App\Enums\User\UserStatus;
use App\Enums\User\UserType;
use App\Exports\UserExport;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

class UserManager extends Component
{
    use WithPagination;

    // Properties for User Management
    public ?User $editingUser = null;
    public $name;
    public $email;
    public $phone;
    public int $status = 1;
    public int $type = 1;
    public int $access;
    public array $selectedRoles = [];

    // Modals & Titles
    public bool $showUserModal = false;
    public string $userModalTitle = '';
    public bool $showPasswordModal = false;

    // Password Management
    public string $password = '';
    public string $password_confirmation = '';

    // Sorting
    public string $sortCol = 'created_at';
    public bool $sortAsc = false;

    // Filter Properties
    public string $search = '';
    public string $filterStatus = '';
    public string $filterType = '';
    public string $filterAccess = '';
    public string $filterRole = '';
    public ?string $filterDateFrom = null;
    public ?string $filterDateTo = null;

    public function mount(): void
    {
        $this->access = UserAccess::user->value;
    }

    protected function userRules(): array
    {
        return [
            'name' => 'required|string|min:3|max:255',
            'email' => ['nullable', 'required_without:phone', 'email', Rule::unique('users')->ignore($this->editingUser?->id)],
            'phone' => ['nullable', 'required_without:email', 'string', Rule::unique('users')->ignore($this->editingUser?->id)],
            'status' => ['required', new Enum(UserStatus::class)],
            'type' => ['required', new Enum(UserType::class)],
            'access' => ['required', new Enum(UserAccess::class)],
            'selectedRoles' => 'nullable|array',
            'selectedRoles.*' => 'exists:roles,name',
            'password' => ['nullable', 'confirmed', Password::min(6)],
        ];
    }

    protected function passwordRules(): array
    {
        return ['password' => ['required', 'confirmed', Password::min(6)]];
    }

    public function updated($propertyName): void
    {
        if (in_array($propertyName, ['search', 'filterStatus', 'filterType', 'filterAccess', 'filterRole', 'filterDateFrom', 'filterDateTo'])) {
            $this->resetPage();
        }
    }

    public function createUser(): void
    {
        $this->resetUserForm();
        $this->userModalTitle = 'ایجاد کاربر جدید';
        $this->showUserModal = true;
    }

    public function editUser(User $user): void
    {
        $this->resetUserForm();
        $this->editingUser = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->status = $user->status->value;
        $this->type = $user->type->value;
        $this->access = $user->access->value;
        $this->selectedRoles = $user->getRoleNames()->toArray();
        $this->userModalTitle = 'ویرایش کاربر';
        $this->showUserModal = true;
    }

    public function saveUser(): void
    {
        $validatedData = $this->validate($this->userRules());

        // Keep track of the original status if we are editing
        $originalStatus = $this->editingUser?->status;

        $rolesToSync = $validatedData['selectedRoles'] ?? [];
        unset($validatedData['selectedRoles']);

        // ADD THIS: Hash password only when creating a new user
        if (!$this->editingUser && !empty($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }
        else {
            // Ensure password is not updated when editing
            unset($validatedData['password']);
        }

        $user = User::updateOrCreate(['id' => $this->editingUser?->id], $validatedData);
        $user->syncRoles($rolesToSync);

        // Check if the user's status was just changed to 'inactive'
        if ($this->editingUser && $originalStatus?->value !== UserStatus::deactive->value && $user->status === UserStatus::deactive) {
            $this->invalidateUserSessions($user);
            $this->dispatch('toast', text: 'کاربر غیرفعال و تمام نشست‌های او خاتمه یافت.', icon: 'info');
        }
        else {
            $message = $this->editingUser ? 'کاربر با موفقیت به‌روزرسانی شد.' : 'کاربر با موفقیت ایجاد شد.';
            $this->dispatch('toast', text: $message, icon: 'success');
        }

        $this->showUserModal = false;
    }

    public function deleteUser(User $user): void
    {
        $user->delete();
        $this->dispatch('toast', text: 'کاربر با موفقیت حذف شد.', icon: 'success');
    }

    public function changePassword(User $user): void
    {
        $this->editingUser = $user;
        $this->resetPasswordForm();
        $this->showPasswordModal = true;
    }

    public function updatePassword(): void
    {
        $validatedData = $this->validate($this->passwordRules());

        if ($this->editingUser) {
            // Update the password
            $this->editingUser->update(['password' => Hash::make($validatedData['password'])]);

            // Invalidate all sessions and tokens for the user
            $this->invalidateUserSessions($this->editingUser);

            $this->dispatch('toast', text: 'رمز عبور به‌روزرسانی و تمام نشست‌ها خاتمه یافت.', icon: 'success');
            $this->showPasswordModal = false;
        }
    }

    public function sortBy($column): void
    {
        if ($this->sortCol === $column) {
            $this->sortAsc = !$this->sortAsc;
        }
        else {
            $this->sortAsc = true;
        }
        $this->sortCol = $column;
    }

    private function getActiveFilters(): array
    {
        return [
            'search' => $this->search,
            'status' => $this->filterStatus,
            'type' => $this->filterType,
            'access' => $this->filterAccess,
            'role' => $this->filterRole,
            'dateFrom' => $this->filterDateFrom,
            'dateTo' => $this->filterDateTo,
        ];
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'filterStatus', 'filterType', 'filterAccess', 'filterRole', 'filterDateFrom', 'filterDateTo');
        $this->resetPage();
    }

    public function resetUserForm(): void
    {
        $this->resetValidation();
        $this->reset('editingUser', 'name', 'email', 'phone', 'status', 'type', 'showUserModal', 'selectedRoles', 'password', 'password_confirmation');
        $this->access = UserAccess::user->value;
    }

    public function resetPasswordForm(): void
    {
        $this->resetValidation();
        $this->reset('password', 'password_confirmation');
    }

    public function export()
    {
        return Excel::download(new UserExport($this->getActiveFilters(), $this->sortCol, $this->sortAsc), 'users.xlsx');
    }

    private function invalidateUserSessions(User $user): void
    {
        // Revoke all Sanctum API tokens
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        // If using the database session driver, delete all of the user's sessions.
        if (config('session.driver') === 'database') {
            DB::table(config('session.table'))
                ->where('user_id', $user->id)
                ->delete();
        }
    }

    public function render()
    {
        $users = User::query()
            ->with('roles')
            ->applyFilters($this->getActiveFilters())
            ->orderBy($this->sortCol, $this->sortAsc ? 'asc' : 'desc')
            ->paginate(15);

        return view('livewire.admin.user.user-manager', [
            'users' => $users,
            'roles' => Role::all(),
        ]);
    }
}
