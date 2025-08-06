<?php

namespace App\Livewire\Shared;

use App\Models\Contact;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\Attributes\Modelable;

/**
 * A reusable component for searching and selecting a Contact.
 *
 * This component provides a search-as-you-type dropdown for finding a contact
 * and uses Livewire's Modelable feature to bind the selected contact's UUID
 * back to a parent component.
 *
 * @property ?string $selectedContactUuid The UUID of the selected contact.
 */
class ContactSelector extends Component
{
    #[Modelable]
    public ?string $selectedContactUuid = null;

    public string $search = '';
    public Collection $contacts;
    public bool $showDropdown = false;
    private bool $contactJustSelected = false;

    public function mount(): void
    {
        $this->contacts = new Collection();
        $this->loadInitialContact();
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
        if ($this->contactJustSelected) {
            $this->contactJustSelected = false;
            return;
        }

        // If a contact was selected but the user is typing a new search, reset the selection.
        if ($this->selectedContactUuid) {
            $selectedContact = Contact::where('uuid', $this->selectedContactUuid)->first();
            if ($selectedContact && $selectedContact->name !== $this->search) {
                $this->selectedContactUuid = null;
            }
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
     * Executes the search query against the database.
     */
    public function performSearch(): void
    {
        $this->contacts = Contact::query()
            ->where('user_id', auth()->id())
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%');
            })
            ->take(7)
            ->get();
    }

    /**
     * Sets the selected contact, updates the input, and hides the dropdown.
     */
    public function selectContact(string $contactUuid): void
    {
        $contact = Contact::where('uuid', $contactUuid)->where('user_id', auth()->id())->first();
        if ($contact) {
            $this->selectedContactUuid = $contact->uuid;
            $this->search = $contact->name;
            $this->showDropdown = false; // This will be entangled with Alpine.
            $this->contactJustSelected = true;
        }
    }

    /**
     * Clears the current selection and search input.
     */
    public function clearSelection(): void
    {
        $this->search = '';
        $this->selectedContactUuid = null;
        $this->contacts = new Collection();
        $this->showDropdown = false; // This will be entangled with Alpine.
    }

    /**
     * If a contact is pre-selected (e.g., in an edit form), load their name.
     */
    public function loadInitialContact(): void
    {
        if ($this->selectedContactUuid) {
            $contact = Contact::where('uuid', $this->selectedContactUuid)->where('user_id', auth()->id())->first();
            if ($contact) {
                $this->search = $contact->name;
            }
        }
    }

    /**
     * Loads a few recent contacts as initial suggestions.
     */
    private function loadInitialSuggestions(): void
    {
        $this->contacts = Contact::where('user_id', auth()->id())
            ->latest('created_at')
            ->take(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.shared.contact-selector');
    }
}

