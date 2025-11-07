<?php

namespace App\Livewire\Admin\Campaign;

use App\Enums\Book\SalesPlatformEnum;
use App\Lib\Campaign\CampaignAnalysisService;
use App\Models\Campaign;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB; // <-- ایمپورت کلاس DB
use Livewire\Component;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;

/**
 * کامپوننت مدیریت لیست کمپین‌ها
 * (Refactored)
 */
class CampaignManager extends Component
{
    use WithPagination;

    // Modals & Stats
    public bool $showStatsModal = false;
    public ?Campaign $viewingCampaign = null;
    public array $campaignStats = [];

    // Sorting
    public string $sortCol = 'created_at';
    public bool $sortAsc = false;

    // Filter Properties
    public string $search = '';
    public string $filterPlatform = '';
    public ?string $filterDateFrom = '';
    public ?string $filterDateTo = '';

    public function updated($propertyName): void
    {
        // Reset pagination on filter change
        if (str_starts_with($propertyName, 'filter') || $propertyName === 'search') {
            $this->resetPage();
        }
    }

    /**
     * Resets all list filters.
     */
    public function resetFilters(): void
    {
        $this->reset('search', 'filterPlatform', 'filterDateFrom', 'filterDateTo');
        $this->resetPage();
    }

    /**
     * Sets sorting column and direction.
     */
    public function sortBy($column): void
    {
        if ($this->sortCol === $column) {
            $this->sortAsc = !$this->sortAsc;
        } else {
            $this->sortAsc = true; // تغییر به true برای مرتب‌سازی صعودی در کلیک اول
        }
        $this->sortCol = $column;
        $this->resetPage(); // ریست کردن صفحه‌بندی هنگام تغییر ستون مرتب‌سازی
    }

    /**
     * Deletes a campaign.
     * `wire:confirm` is handled in the view (Instruction #17)
     */
    public function deleteCampaign(Campaign $campaign): void
    {
        $campaign->books()->detach(); // Clean up pivot
        $campaign->delete();
        $this->dispatch('toast', text: 'کمپین با موفقیت حذف شد.', icon: 'success'); // Instruction #18
    }


    // --- Analysis Methods ---

    /**
     * Shows the statistics modal for a campaign.
     */
    public function showCampaignStats(Campaign $campaign, CampaignAnalysisService $analysisService): void
    {
        $this->viewingCampaign = $campaign;

        // Use the dedicated logic class (Instruction #3)
        $this->campaignStats = $analysisService->calculateCampaignStatistics($campaign);

        $this->showStatsModal = true;
    }

    /**
     * Creates and returns the base query for campaigns with all filters applied.
     * (Separation of concerns, Instruction #3)
     */
    protected function getCampaignsQuery(): Builder
    {
        // Handle Jalali date conversion for filters
        $filterDateFrom = $this->filterDateFrom
            ? Jalalian::fromFormat('Y/m/d', $this->filterDateFrom)->toCarbon()->format('Y-m-d')
            : null;
        $filterDateTo = $this->filterDateTo
            ? Jalalian::fromFormat('Y/m/d', $this->filterDateTo)->toCarbon()->format('Y-m-d')
            : null;

        // --- REFACTOR: START ---
        // (Instruction #6 & #10: Optimized subqueries for sales stats)

        // Subquery for Total Sales Count
        $salesCountQuery = DB::table('payments')
            ->select(DB::raw('COALESCE(COUNT(payments.id), 0)'))
            ->join('book_campaign_pivot', 'payments.book_id', '=', 'book_campaign_pivot.book_id')
            ->whereColumn('book_campaign_pivot.campaign_id', 'campaigns.id')
            ->whereColumn('payments.sale_platform', 'campaigns.platform') // Match campaign platform
            ->whereBetweenColumns('payments.sale_date', ['campaigns.start_date', 'campaigns.end_date']); // Match campaign dates

        // Subquery for Total Sales Amount
        $salesAmountQuery = DB::table('payments')
            ->select(DB::raw('COALESCE(SUM(payments.amount), 0)'))
            ->join('book_campaign_pivot', 'payments.book_id', '=', 'book_campaign_pivot.book_id')
            ->whereColumn('book_campaign_pivot.campaign_id', 'campaigns.id')
            ->whereColumn('payments.sale_platform', 'campaigns.platform')
            ->whereBetweenColumns('payments.sale_date', ['campaigns.start_date', 'campaigns.end_date']);

        // --- REFACTOR: END ---

        return Campaign::query()
            ->withCount('books') // Optimized query (Instruction #6)
            // Add the subqueries as selectable and sortable columns
            ->selectSub($salesCountQuery, 'total_sales_count')
            ->selectSub($salesAmountQuery, 'total_sales_amount')
            ->when($this->search, fn($query, $search) => $query->where('name', 'like', '%' . $search . '%'))
            ->when($this->filterPlatform, fn($query, $platform) => $query->where('platform', $platform))
            ->when($filterDateFrom, fn($query) => $query->where('start_date', '>=', $filterDateFrom))
            ->when($filterDateTo, fn($query) => $query->where('end_date', '<=', $filterDateTo));
    }


    /**
     * Renders the component view.
     */
    public function render()
    {
        $query = $this->getCampaignsQuery();

        // (Refactored)
        // اطمینان از اینکه مرتب‌سازی ستون‌های آماری (که با selectSub اضافه شده‌اند) به درستی کار می‌کند
        // Eloquent/MySQL به اندازه کافی هوشمند هستند که نام مستعار (alias) را در orderBy تشخیص دهند
        $campaigns = $query->orderBy($this->sortCol, $this->sortAsc ? 'asc' : 'desc')
            ->paginate(15);

        return view('livewire.admin.campaign.campaign-manager', [
            'campaigns' => $campaigns,
            'platforms' => SalesPlatformEnum::cases(),
        ]);
    }
}
