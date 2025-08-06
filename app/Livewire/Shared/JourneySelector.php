<?php

namespace App\Livewire\Shared;

use App\Models\Journey;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\Attributes\Modelable;

/**
 * A reusable component for searching and selecting a Journey.
 *
 * @property ?int $selectedJourneyId The ID of the selected journey.
 */
class JourneySelector extends Component
{
    #[Modelable]
    public ?int $selectedJourneyId = null;

    public string $search = '';
    public Collection $journeys;
    public bool $showDropdown = false;
    private bool $journeyJustSelected = false;

    public function mount(): void
    {
        $this->journeys = new Collection();
        $this->loadInitialJourney();
    }

    public function handleFocus(): void
    {
        $this->showDropdown = true;
        if (empty($this->search)) {
            $this->loadInitialSuggestions();
        }
    }

    public function updatedSearch(): void
    {
        if ($this->journeyJustSelected) {
            $this->journeyJustSelected = false;
            return;
        }

        if ($this->selectedJourneyId) {
            $selectedJourney = Journey::find($this->selectedJourneyId);
            if ($selectedJourney && $selectedJourney->name !== $this->search) {
                $this->selectedJourneyId = null;
            }
        }

        if (strlen($this->search) > 0) {
            $this->performSearch();
        } else {
            $this->loadInitialSuggestions();
        }
        $this->showDropdown = true;
    }

    public function performSearch(): void
    {
        $this->journeys = Journey::query()
            ->where('user_id', auth()->id())
            ->where('name', 'like', '%' . $this->search . '%')
            ->take(7)
            ->get();
    }

    public function selectJourney(int $journeyId): void
    {
        $journey = Journey::find($journeyId);
        if ($journey) {
            $this->selectedJourneyId = $journey->id;
            $this->search = $journey->name;
            $this->showDropdown = false;
            $this->journeyJustSelected = true;
        }
    }

    public function clearSelection(): void
    {
        $this->search = '';
        $this->selectedJourneyId = null;
        $this->journeys = new Collection();
        $this->showDropdown = false;
    }

    public function loadInitialJourney(): void
    {
        if ($this->selectedJourneyId) {
            $journey = Journey::find($this->selectedJourneyId);
            if ($journey) {
                $this->search = $journey->name;
            }
        }
    }

    private function loadInitialSuggestions(): void
    {
        $this->journeys = Journey::query()
            ->where('user_id', auth()->id())
            ->latest('created_at')
            ->take(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.shared.journey-selector');
    }
}
