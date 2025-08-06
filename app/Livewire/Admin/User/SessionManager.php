<?php

namespace App\Livewire\Admin\User;

use App\Models\Session;
use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;


class SessionManager extends Component
{
    use WithPagination;

    // Sorting
    public string $sortCol = 'last_activity';
    public bool $sortAsc = false;

    // Filter Properties
    public string $search = '';
    public bool $filterOnlyAdmins = false;

    /**
     * Runs when a filter property is updated, resets pagination.
     */
    public function updated($propertyName): void
    {
        if (in_array($propertyName, ['search', 'filterOnlyAdmins'])) {
            $this->resetPage();
        }
    }

    /**
     * Sets the sorting column and direction.
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
     * Deletes a specific session after confirmation.
     */
    public function deleteSession(Session $session): void
    {
        $session->delete();
        $this->dispatch('toast', text: 'نشست با موفقیت حذف شد.', icon: 'success');
    }

    /**
     * Resets all active filters.
     */
    public function clearFilters(): void
    {
        $this->reset('search', 'filterOnlyAdmins');
        $this->resetPage();
    }

    /**
     * Renders the component, fetches data, and performs analysis.
     */
    public function render()
    {
        // Base query with eager loading for performance
        $query = Session::query()
            ->with('user')
            ->whereNotNull('user_id');

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('ip_address', 'like', '%' . $this->search . '%')
                    ->orWhere('user_agent', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', function ($userQuery) {
                        $userQuery->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%');
                    });
            });
        }

        // Apply admin-only filter
        if ($this->filterOnlyAdmins) {
            $query->whereHas('user', fn ($q) => $q->where('access', 2));
        }

        // Clone the query before pagination to perform analysis on the full filtered dataset
        $analysisQuery = clone $query;
        $allFilteredSessions = $analysisQuery->get();

        // Perform data analysis
        $stats = $this->analyzeSessions($allFilteredSessions);

        // Apply sorting and paginate the results for display
        $sessions = $query
            ->orderBy($this->sortCol, $this->sortAsc ? 'asc' : 'desc')
            ->paginate(15);

        return view('livewire.admin.user.session-manager', [
            'sessions' => $sessions,
            'stats' => $stats,
        ]);
    }

    /**
     * Analyzes a collection of sessions to generate useful statistics.
     *
     * @param Collection $sessions
     * @return array
     */
    private function analyzeSessions(Collection $sessions): array
    {
        $platformCounts = ['Desktop' => 0, 'Mobile' => 0];
        $browserCounts = [];

        foreach ($sessions as $session) {
            // Call the model method with `true` to get parsed data
            $agentInfo = $session->user_agent(true);

            // Count platforms
            if (in_array($agentInfo['platform'], ['Windows 10', 'Windows 8.1', 'Windows 7', 'macOS', 'Linux'])) {
                $platformCounts['Desktop']++;
            } elseif (in_array($agentInfo['platform'], ['Android', 'iOS'])) {
                $platformCounts['Mobile']++;
            }

            // Count browsers
            $browserName = explode(' ', $agentInfo['browser'])[0];
            if ($browserName !== 'Unknown') {
                $browserCounts[$browserName] = ($browserCounts[$browserName] ?? 0) + 1;
            }
        }

        arsort($browserCounts);

        return [
            'total' => $sessions->count(),
            'admins' => $sessions->where('user.access', 2)->count(),
            'platforms' => $platformCounts,
            'top_browser' => count($browserCounts) > 0 ? key($browserCounts) : 'N/A',
        ];
    }
}
