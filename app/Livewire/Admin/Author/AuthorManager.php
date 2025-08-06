<?php

namespace App\Livewire\Admin\Author;

use Livewire\Component;

use App\Models\Author;
use Livewire\WithPagination;

class AuthorManager extends Component
{
    use WithPagination;

    // Properties for Author Management
    public ?Author $editingAuthor = null;
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
        $authors = Author::query()
            ->withCount('books') // Eager load book count
            ->when($this->search, function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->orderBy($this->sortCol, $this->sortAsc ? 'asc' : 'desc')
            ->paginate(10);

        return view('livewire.admin.author.author-manager', [
            'authors' => $authors,
        ]);
    }
}

