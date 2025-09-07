<?php

namespace App\Livewire\Admin\Editor;

use App\Exports\EditorsExport;
use App\Models\Editor;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class EditorManager extends Component
{
    use WithPagination;

    // Properties for Editor Management
    public ?Editor $editingEditor = null;
    public $name = '';
    public $description = '';

    // Modal & Title
    public bool $showModal = false;
    public string $modalTitle = '';

    // Search & Sorting
    public string $search = '';
    public string $sortCol = 'created_at';
    public bool $sortAsc = false;

    /**
     * Validation rules for saving an editor.
     */
    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:255|unique:editors,name,' . $this->editingEditor?->id,
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
        $this->reset('editingEditor', 'name', 'description');
    }

    /**
     * Opens the modal to create a new editor.
     */
    public function create(): void
    {
        $this->resetForm();
        $this->modalTitle = 'ایجاد ویراستار جدید';
        $this->showModal = true;
    }

    /**
     * Opens the modal to edit an existing editor.
     */
    public function edit(Editor $editor): void
    {
        $this->resetForm();
        $this->editingEditor = $editor;
        $this->name = $editor->name;
        $this->description = $editor->description;
        $this->modalTitle = 'ویرایش ویراستار';
        $this->showModal = true;
    }

    /**
     * Saves the new or edited editor to the database.
     */
    public function save(): void
    {
        $validatedData = $this->validate();

        Editor::updateOrCreate(['id' => $this->editingEditor?->id], $validatedData);

        $message = $this->editingEditor ? 'ویراستار با موفقیت به‌روزرسانی شد.' : 'ویراستار با موفقیت ایجاد شد.';
        $this->dispatch('toast', text: $message, icon: 'success');

        $this->showModal = false;
    }

    /**
     * Deletes an editor after confirmation.
     */
    public function delete(Editor $editor): void
    {
        if ($editor->books()->exists()) {
            $this->dispatch('toast', text: 'امکان حذف وجود ندارد. کتاب‌هایی به این ویراستار اختصاص دارند.', icon: 'error');
            return;
        }

        $editor->delete();
        $this->dispatch('toast', text: 'ویراستار با موفقیت حذف شد.', icon: 'success');
    }

    /**
     * Sorts the editor list by the given column.
     */
    public function sortBy(string $column): void
    {
        if ($this->sortCol === $column) {
            $this->sortAsc = !$this->sortAsc;
        }
        else {
            $this->sortAsc = true;
        }
        $this->sortCol = $column;
    }

    /**
     * Creates and returns the base query for editors with filters and sorting.
     */
    protected function getEditorsQuery(): Builder
    {
        return Editor::query()
            ->withCount('books')
            ->when($this->search, function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');;
            })
            ->orderBy($this->sortCol, $this->sortAsc ? 'asc' : 'desc');
    }

    /**
     * Exports the filtered data to an Excel file.
     */
    public function exportExcel()
    {
        $query = $this->getEditorsQuery();

        return Excel::download(
            new EditorsExport($query),
            'editors-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Renders the component.
     */
    public function render()
    {
        $editors = $this->getEditorsQuery()->paginate(10);

        return view('livewire.admin.editor.editor-manager', [
            'editors' => $editors,
        ]);
    }
}
