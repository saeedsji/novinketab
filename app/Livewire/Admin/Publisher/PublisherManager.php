<?php

namespace App\Livewire\Admin\Publisher;

use Livewire\Component;

use App\Models\Publisher;
use Livewire\WithPagination;

class PublisherManager extends Component
{
    use WithPagination;

    // Properties for Publisher Management
    public ?Publisher $editingPublisher = null;
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
     * Validation rules for saving a publisher.
     */
    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:255|unique:publishers,name,' . $this->editingPublisher?->id,
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
        $this->reset('editingPublisher', 'name', 'description');
    }

    /**
     * Opens the modal to create a new publisher.
     */
    public function create(): void
    {
        $this->resetForm();
        $this->modalTitle = 'ایجاد ناشر جدید';
        $this->showModal = true;
    }

    /**
     * Opens the modal to edit an existing publisher.
     */
    public function edit(Publisher $publisher): void
    {
        $this->resetForm();
        $this->editingPublisher = $publisher;
        $this->name = $publisher->name;
        $this->description = $publisher->description;
        $this->modalTitle = 'ویرایش ناشر';
        $this->showModal = true;
    }

    /**
     * Saves the new or edited publisher to the database.
     */
    public function save(): void
    {
        $validatedData = $this->validate();

        Publisher::updateOrCreate(['id' => $this->editingPublisher?->id], $validatedData);

        $message = $this->editingPublisher ? 'ناشر با موفقیت به‌روزرسانی شد.' : 'ناشر با موفقیت ایجاد شد.';
        $this->dispatch('toast', text: $message, icon: 'success');

        $this->showModal = false;
    }

    /**
     * Deletes a publisher after confirmation.
     */
    public function delete(Publisher $publisher): void
    {
        if ($publisher->books()->exists()) {
            $this->dispatch('toast', text: 'امکان حذف وجود ندارد. کتاب‌هایی به این ناشر اختصاص دارند.', icon: 'error');
            return;
        }

        $publisher->delete();
        $this->dispatch('toast', text: 'ناشر با موفقیت حذف شد.', icon: 'success');
    }

    /**
     * Sorts the publisher list by the given column.
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
        $publishers = Publisher::query()
            ->withCount('books') // Eager load book count
            ->when($this->search, function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->orderBy($this->sortCol, $this->sortAsc ? 'asc' : 'desc')
            ->paginate(10);

        return view('livewire.admin.publisher.publisher-manager', [
            'publishers' => $publishers,
        ]);
    }
}

