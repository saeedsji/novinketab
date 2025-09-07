<?php

namespace App\Livewire\Admin\Publisher;

use App\Exports\PublishersExport;
use App\Models\Publisher;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\Builder;


class PublisherManager extends Component
{
    use WithPagination;

    // Properties for Publisher Management
    public ?Publisher $editingPublisher = null;
    public  $name = '';
    public  $description = '';
    public  $share_percent = 0; // New property for publisher's share

    // Modal & Title
    public bool $showModal = false;
    public string $modalTitle = '';

    // Search, Sorting & Filtering
    public string $search = '';
    public string $sortCol = 'created_at';
    public bool $sortAsc = false;
    public ?int $filter_share_percent_min = null;
    public ?int $filter_share_percent_max = null;


    /**
     * Validation rules for saving a publisher.
     */
    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:255|unique:publishers,name,' . $this->editingPublisher?->id,
            'description' => 'nullable|string|max:1000',
            'share_percent' => 'required|integer|min:0|max:100', // Validation for the new field
        ];
    }

    /**
     * Reset pagination when searching or filtering.
     */
    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedFilterSharePercentMin(): void { $this->resetPage(); }
    public function updatedFilterSharePercentMax(): void { $this->resetPage(); }

    /**
     * Resets the form fields to their default state.
     */
    public function resetForm(): void
    {
        $this->resetValidation();
        $this->reset('editingPublisher', 'name', 'description', 'share_percent');
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
        $this->share_percent = $publisher->share_percent; // Load share_percent for editing
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
     * Creates and returns the base query for publishers with filters and sorting.
     * This method centralizes the query logic.
     */
    protected function getPublishersQuery(): Builder
    {
        return Publisher::query()
            ->withCount('books')
            ->when($this->search, function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            })
            ->when($this->filter_share_percent_min, function ($query, $min) {
                $query->where('share_percent', '>=', $min);
            })
            ->when($this->filter_share_percent_max, function ($query, $max) {
                $query->where('share_percent', '<=', $max);
            })
            ->orderBy($this->sortCol, $this->sortAsc ? 'asc' : 'desc');
    }

    /**
     * Exports the filtered data to an Excel file using the centralized query.
     */
    public function exportExcel()
    {
        // Use the centralized query method
        $query = $this->getPublishersQuery();

        return Excel::download(
            new PublishersExport($query),
            'publishers-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Renders the component.
     */
    public function render()
    {
        // Paginate the results from the centralized query method
        $publishers = $this->getPublishersQuery()->paginate(10);

        return view('livewire.admin.publisher.publisher-manager', [
            'publishers' => $publishers,
        ]);
    }
}
