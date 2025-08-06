<?php

namespace App\Livewire\Admin\Composer;

use Livewire\Component;
use App\Models\Composer;
use Livewire\WithPagination;

class ComposerManager extends Component
{
    use WithPagination;

    // Properties for Composer Management
    public ?Composer $editingComposer = null;
    public string $name = '';
    public string $description = '';

    // Modal & Title
    public bool $showModal = false;
    public string $modalTitle = '';

    // Search & Sorting
    public string $search = '';
    public string $sortCol = 'created_at';
    public bool $sortAsc = false;

    /**
     * Validation rules for saving a composer.
     */
    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:255|unique:composers,name,' . $this->editingComposer?->id,
            'description' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Reset pagination when searching.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Resets the form fields to their default state.
     */
    public function resetForm(): void
    {
        $this->resetValidation();
        $this->reset('editingComposer', 'name', 'description');
    }

    /**
     * Opens the modal to create a new composer.
     */
    public function create(): void
    {
        $this->resetForm();
        $this->modalTitle = 'ایجاد آهنگساز جدید';
        $this->showModal = true;
    }

    /**
     * Opens the modal to edit an existing composer.
     */
    public function edit(Composer $composer): void
    {
        $this->resetForm();
        $this->editingComposer = $composer;
        $this->name = $composer->name;
        $this->description = $composer->description;
        $this->modalTitle = 'ویرایش آهنگساز';
        $this->showModal = true;
    }

    /**
     * Saves the new or edited composer to the database.
     */
    public function save(): void
    {
        $validatedData = $this->validate();

        Composer::updateOrCreate(['id' => $this->editingComposer?->id], $validatedData);

        $message = $this->editingComposer ? 'آهنگساز با موفقیت به‌روزرسانی شد.' : 'آهنگساز با موفقیت ایجاد شد.';
        $this->dispatch('toast', text: $message, icon: 'success');

        $this->showModal = false;
    }

    /**
     * Deletes a composer after confirmation.
     */
    public function delete(Composer $composer): void
    {
        if ($composer->books()->exists()) {
            $this->dispatch('toast', text: 'امکان حذف وجود ندارد. کتاب‌هایی به این آهنگساز اختصاص دارند.', icon: 'error');
            return;
        }

        $composer->delete();
        $this->dispatch('toast', text: 'آهنگساز با موفقیت حذف شد.', icon: 'success');
    }

    /**
     * Sorts the composer list by the given column.
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
     * Renders the component.
     */
    public function render()
    {
        $composers = Composer::query()
            ->withCount('books') // Eager load book count
            ->when($this->search, function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->orderBy($this->sortCol, $this->sortAsc ? 'asc' : 'desc')
            ->paginate(10);

        return view('livewire.admin.composer.composer-manager', [
            'composers' => $composers,
        ]);
    }
}
