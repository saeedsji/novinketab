<?php

namespace App\Livewire\Shared;

use App\Models\Event;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\Attributes\Modelable;

/**
 * A reusable component for searching and selecting a unique Event name.
 *
 * This component provides a search-as-you-type dropdown for finding an event name
 * and uses Livewire's Modelable feature to bind the selected event name
 * back to a parent component. It uses Alpine's @entangle for a smooth UX.
 *
 * @property ?string $selectedEventName The name of the selected event.
 */
class EventSelector extends Component
{
    #[Modelable]
    public ?string $selectedEventName = null;

    public string $search = '';
    public Collection $eventNames;
    public bool $showDropdown = false;
    private bool $eventJustSelected = false;

    public function mount(): void
    {
        $this->eventNames = new Collection();
        $this->loadInitialEventName();
    }

    /**
     * Show the dropdown and load initial suggestions on focus.
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
        if ($this->eventJustSelected) {
            $this->eventJustSelected = false;
            return;
        }

        // If an event was selected but the user is typing a new search, reset the selection.
        if ($this->selectedEventName && $this->selectedEventName !== $this->search) {
            $this->selectedEventName = null;
        }

        if (strlen($this->search) > 0) {
            $this->performSearch();
        } else {
            // If search is cleared, show initial suggestions again.
            $this->loadInitialSuggestions();
        }
        // Ensure the dropdown stays open during search.
        $this->showDropdown = true;
    }

    /**
     * Executes the search query for unique event names.
     */
    public function performSearch(): void
    {
        $this->eventNames = Event::query()
            ->where('user_id', auth()->id())
            ->select('name')
            ->distinct()
            ->where('name', 'like', '%' . $this->search . '%')
            ->take(7)
            ->pluck('name');
    }

    /**
     * Sets the selected event, updates the input, and hides the dropdown.
     */
    public function selectEvent(string $eventName): void
    {
        $this->selectedEventName = $eventName;
        $this->search = $eventName;
        $this->showDropdown = false; // This will be entangled with Alpine.
        $this->eventJustSelected = true;
    }

    /**
     * Clears the current selection and search input.
     */
    public function clearSelection(): void
    {
        $this->search = '';
        $this->selectedEventName = null;
        $this->eventNames = new Collection();
        $this->showDropdown = false; // This will be entangled with Alpine.
    }

    /**
     * If an event name is pre-selected, set the search input text.
     */
    public function loadInitialEventName(): void
    {
        if ($this->selectedEventName) {
            $this->search = $this->selectedEventName;
        }
    }

    /**
     * Loads a few recent unique event names as initial suggestions.
     * This method is updated to prevent the SQL error.
     */
    private function loadInitialSuggestions(): void
    {
        // First, get a collection of the most recent events.
        $recentEvents = Event::query()
            ->where('user_id', auth()->id())
            ->latest('created_at') // Order by date first
            ->select('name')
            ->limit(50) // Get a reasonable number of recent records to scan for unique names
            ->get();

        // Now, get the unique names from this collection in memory and take the top 5.
        $this->eventNames = $recentEvents->pluck('name')->unique()->take(5)->values();
    }

    public function render()
    {
        return view('livewire.shared.event-selector');
    }
}
