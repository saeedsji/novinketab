<?php

namespace App\Livewire\Admin\Author;

use App\Exports\AuthorsExport;
use App\Models\Author;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class AuthorManager extends Component
{
    use WithPagination;

    // Properties for Author Management
    public ?Author $editingAuthor = null;
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
     * Validation rules for saving an author.
     */
    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:255|unique:authors,name,' . $this->editingAuthor?->id,
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
        $this->reset('editingAuthor', 'name', 'description');
    }

    /**
     * Opens the modal to create a new author.
     */
    public function create(): void
    {
        $this->resetForm();
        $this->modalTitle = 'ایجاد نویسنده جدید';
        $this->showModal = true;
    }

    /**
     * Opens the modal to edit an existing author.
     */
    public function edit(Author $author): void
    {
        $this->resetForm();
        $this->editingAuthor = $author;
        $this->name = $author->name;
        $this->description = $author->description;
        $this->modalTitle = 'ویرایش نویسنده';
        $this->showModal = true;
    }

    /**
     * Saves the new or edited author to the database.
     */
    public function save(): void
    {
        $validatedData = $this->validate();

        Author::updateOrCreate(['id' => $this->editingAuthor?->id], $validatedData);

        $message = $this->editingAuthor ? 'نویسنده با موفقیت به‌روزرسانی شد.' : 'نویسنده با موفقیت ایجاد شد.';
        $this->dispatch('toast', text: $message, icon: 'success');

        $this->showModal = false;
    }

    /**
     * Deletes an author after confirmation.
     */
    public function delete(Author $author): void
    {
        if ($author->books()->exists()) {
            $this->dispatch('toast', text: 'امکان حذف وجود ندارد. کتاب‌هایی به این نویسنده اختصاص دارند.', icon: 'error');
            return;
        }

        $author->delete();
        $this->dispatch('toast', text: 'نویسنده با موفقیت حذف شد.', icon: 'success');
    }

    /**
     * Sorts the author list by the given column.
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
     * Creates and returns the base query for authors with filters and sorting.
     */
    protected function getAuthorsQuery(): Builder
    {
        return Author::query()
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
        $query = $this->getAuthorsQuery();

        return Excel::download(
            new AuthorsExport($query),
            'authors-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Renders the component.
     */
    public function render()
    {
        $authors = $this->getAuthorsQuery()->paginate(10);

        return view('livewire.admin.author.author-manager', [
            'authors' => $authors,
        ]);
    }
}
