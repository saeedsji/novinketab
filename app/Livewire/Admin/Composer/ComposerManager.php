<?php

namespace App\Livewire\Admin\Composer;

use App\Exports\ComposersExport;
use App\Models\Composer;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class ComposerManager extends Component
{
    use WithPagination;

    // Properties for Composer Management
    public ?Composer $editingComposer = null;
    public ?string $name = null;
    public ?string $description = null;

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
    public function sortBy(string $column): void
    {
        if ($this->sortCol === $column) {
            $this->sortAsc = !$this->sortAsc;
        } else {
            $this->sortAsc = true;
        }
        $this->sortCol = $column;
    }

    /**
     * Creates and returns the base query for composers with filters and sorting.
     */
    protected function getComposersQuery(): Builder
    {
        return Composer::query()
            ->withCount('books')
            ->when($this->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            })
            ->orderBy($this->sortCol, $this->sortAsc ? 'asc' : 'desc');
    }

    /**
     * Exports the filtered data to an Excel file.
     */
    public function exportExcel()
    {
        $query = $this->getComposersQuery();

        return Excel::download(
            new ComposersExport($query),
            'composers-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Renders the component.
     */
    public function render()
    {
        $composers = $this->getComposersQuery()->paginate(10);

        return view('livewire.admin.composer.composer-manager', [
            'composers' => $composers,
        ]);
    }
}
