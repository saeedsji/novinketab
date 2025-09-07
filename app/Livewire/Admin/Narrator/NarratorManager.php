<?php

namespace App\Livewire\Admin\Narrator;

use App\Exports\NarratorsExport;
use App\Models\Narrator;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class NarratorManager extends Component
{
    use WithPagination;

    // Properties for Narrator Management
    public ?Narrator $editingNarrator = null;
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
     * Validation rules for saving a narrator.
     */
    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:255|unique:narrators,name,' . $this->editingNarrator?->id,
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
        $this->reset('editingNarrator', 'name', 'description');
    }

    /**
     * Opens the modal to create a new narrator.
     */
    public function create(): void
    {
        $this->resetForm();
        $this->modalTitle = 'ایجاد گوینده جدید';
        $this->showModal = true;
    }

    /**
     * Opens the modal to edit an existing narrator.
     */
    public function edit(Narrator $narrator): void
    {
        $this->resetForm();
        $this->editingNarrator = $narrator;
        $this->name = $narrator->name;
        $this->description = $narrator->description;
        $this->modalTitle = 'ویرایش گوینده';
        $this->showModal = true;
    }

    /**
     * Saves the new or edited narrator to the database.
     */
    public function save(): void
    {
        $validatedData = $this->validate();

        Narrator::updateOrCreate(['id' => $this->editingNarrator?->id], $validatedData);

        $message = $this->editingNarrator ? 'گوینده با موفقیت به‌روزرسانی شد.' : 'گوینده با موفقیت ایجاد شد.';
        $this->dispatch('toast', text: $message, icon: 'success');

        $this->showModal = false;
    }

    /**
     * Deletes a narrator after confirmation.
     */
    public function delete(Narrator $narrator): void
    {
        if ($narrator->books()->exists()) {
            $this->dispatch('toast', text: 'امکان حذف وجود ندارد. کتاب‌هایی به این گوینده اختصاص دارند.', icon: 'error');
            return;
        }

        $narrator->delete();
        $this->dispatch('toast', text: 'گوینده با موفقیت حذف شد.', icon: 'success');
    }

    /**
     * Sorts the narrator list by the given column.
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
     * Creates and returns the base query for narrators with filters and sorting.
     */
    protected function getNarratorsQuery(): Builder
    {
        return Narrator::query()
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
        $query = $this->getNarratorsQuery();

        return Excel::download(
            new NarratorsExport($query),
            'narrators-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Renders the component.
     */
    public function render()
    {
        $narrators = $this->getNarratorsQuery()->paginate(10);

        return view('livewire.admin.narrator.narrator-manager', [
            'narrators' => $narrators,
        ]);
    }
}
