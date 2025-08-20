<?php

namespace App\Livewire\Shared;

use App\Models\Book;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Modelable;
use Livewire\Component;

/**
 * A reusable component for searching and selecting a Book.
 *
 * This component provides a search-as-you-type dropdown for finding a book
 * and uses Livewire's Modelable feature to bind the selected book's ID
 * back to a parent component.
 *
 * @property ?int $selectedBookId The ID of the selected book.
 */
class BookSelector extends Component
{
    #[Modelable]
    public ?int $selectedBookId = null;

    public string $search = '';
    public Collection $books;
    public bool $showDropdown = false;
    private bool $bookJustSelected = false;

    public function mount(): void
    {
        $this->books = new Collection();
        $this->loadInitialBook();
    }

    /**
     * Show the dropdown and load initial suggestions when the input is focused.
     */
    public function handleFocus(): void
    {
        $this->showDropdown = true;
        if (empty($this->search)) {
            $this->loadInitialSuggestions();
        }
    }

    /**
     * Lifecycle hook that runs when the search input changes.
     */
    public function updatedSearch(): void
    {
        if ($this->bookJustSelected) {
            $this->bookJustSelected = false;
            return;
        }

        // If a book was selected but the user is typing a new search, reset the selection.
        if ($this->selectedBookId) {
            $selectedBook = Book::find($this->selectedBookId);
            if ($selectedBook && $selectedBook->title !== $this->search) {
                $this->selectedBookId = null;
            }
        }

        if (strlen($this->search) > 0) {
            $this->performSearch();
        } else {
            $this->loadInitialSuggestions();
        }
        $this->showDropdown = true;
    }

    /**
     * Executes the search query against the database.
     */
    public function performSearch(): void
    {
        // Optimized query to search by title or financial code
        $this->books = Book::query()
            ->where(function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%')
                    ->orWhere('financial_code', 'like', '%' . $this->search . '%');
            })
            ->take(7)
            ->get(['id', 'title', 'financial_code']); // Select only necessary columns
    }

    /**
     * Sets the selected book, updates the input, and hides the dropdown.
     */
    public function selectBook(int $bookId): void
    {
        $book = Book::find($bookId);
        if ($book) {
            $this->selectedBookId = $book->id;
            $this->search = $book->title;
            $this->showDropdown = false;
            $this->bookJustSelected = true;
        }
    }

    /**
     * Clears the current selection and search input.
     */
    public function clearSelection(): void
    {
        $this->search = '';
        $this->selectedBookId = null;
        $this->books = new Collection();
        $this->showDropdown = false;
    }

    /**
     * If a book is pre-selected (e.g., in an edit form), load its title.
     */
    public function loadInitialBook(): void
    {
        if ($this->selectedBookId) {
            $book = Book::find($this->selectedBookId);
            if ($book) {
                $this->search = $book->title;
            }
        }
    }

    /**
     * Loads a few recent books as initial suggestions.
     */
    private function loadInitialSuggestions(): void
    {
        $this->books = Book::latest('created_at')
            ->take(5)
            ->get(['id', 'title', 'financial_code']);
    }

    public function render()
    {
        return view('livewire.shared.book-selector');
    }
}
