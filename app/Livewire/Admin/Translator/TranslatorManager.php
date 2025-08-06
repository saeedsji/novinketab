<?php

namespace App\Livewire\Admin\Translator;

use Livewire\Component;
use App\Models\Translator;
use Livewire\WithPagination;

class TranslatorManager extends Component
{
    use WithPagination;

    // Properties for Translator Management
    public ?Translator $editingTranslator = null;
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
        $translators = Translator::query()
            ->withCount('books') // Eager load book count
            ->when($this->search, function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->orderBy($this->sortCol, $this->sortAsc ? 'asc' : 'desc')
            ->paginate(10);

        return view('livewire.admin.translator.translator-manager', [
            'translators' => $translators,
        ]);
    }
}

