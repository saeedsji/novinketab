<?php

namespace App\Livewire\Admin\Translator;

use App\Exports\TranslatorsExport;
use App\Models\Translator;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class TranslatorManager extends Component
{
    use WithPagination;

    // Properties for Translator Management
    public ?Translator $editingTranslator = null;
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
     * Validation rules for saving a translator.
     */
    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:255|unique:translators,name,' . $this->editingTranslator?->id,
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
        $this->reset('editingTranslator', 'name', 'description');
    }

    /**
     * Opens the modal to create a new translator.
     */
    public function create(): void
    {
        $this->resetForm();
        $this->modalTitle = 'ایجاد مترجم جدید';
        $this->showModal = true;
    }

    /**
     * Opens the modal to edit an existing translator.
     */
    public function edit(Translator $translator): void
    {
        $this->resetForm();
        $this->editingTranslator = $translator;
        $this->name = $translator->name;
        $this->description = $translator->description;
        $this->modalTitle = 'ویرایش مترجم';
        $this->showModal = true;
    }

    /**
     * Saves the new or edited translator to the database.
     */
    public function save(): void
    {
        $validatedData = $this->validate();

        Translator::updateOrCreate(['id' => $this->editingTranslator?->id], $validatedData);

        $message = $this->editingTranslator ? 'مترجم با موفقیت به‌روزرسانی شد.' : 'مترجم با موفقیت ایجاد شد.';
        $this->dispatch('toast', text: $message, icon: 'success');

        $this->showModal = false;
    }

    /**
     * Deletes a translator after confirmation.
     */
    public function delete(Translator $translator): void
    {
        if ($translator->books()->exists()) {
            $this->dispatch('toast', text: 'امکان حذف وجود ندارد. کتاب‌هایی به این مترجم اختصاص دارند.', icon: 'error');
            return;
        }

        $translator->delete();
        $this->dispatch('toast', text: 'مترجم با موفقیت حذف شد.', icon: 'success');
    }

    /**
     * Sorts the translator list by the given column.
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
     * Creates and returns the base query for translators with filters and sorting.
     */
    protected function getTranslatorsQuery(): Builder
    {
        return Translator::query()
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
        $query = $this->getTranslatorsQuery();

        return Excel::download(
            new TranslatorsExport($query),
            'translators-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Renders the component.
     */
    public function render()
    {
        $translators = $this->getTranslatorsQuery()->paginate(10);

        return view('livewire.admin.translator.translator-manager', [
            'translators' => $translators,
        ]);
    }
}
