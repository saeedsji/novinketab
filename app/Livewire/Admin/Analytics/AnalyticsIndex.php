<?php

namespace App\Livewire\Admin\Analytics;

use App\Lib\Analytics\AnalyticsService;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class AnalyticsIndex extends Component
{
    use WithPagination;

    public ?string $startDate = '';
    public ?string $endDate = '';

    public function mount()
    {
        $this->endDate = Carbon::now()->toDateString();
        $this->startDate = Carbon::now()->subDays(30)->toDateString();
    }

    public function updated($property)
    {
        // ریست کردن صفحه بندی هنگام تغییر فیلترها
        if (in_array($property, ['startDate', 'endDate'])) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $analyticsService = new AnalyticsService($this->startDate, $this->endDate);

        $comprehensiveStats = $analyticsService->getComprehensiveStats();
        $recentPayments = $analyticsService->getRecentPayments(20);
        $topAuthors = $analyticsService->getTopAuthors(20);

        $allChartData = $analyticsService->getAllChartData();
        $this->dispatch('updateAllCharts', chartsData: $allChartData);

        return view('livewire.admin.analytics.analytics-index', [
            'stats' => $comprehensiveStats,
            'recentPayments' => $recentPayments,
            'topAuthors' => $topAuthors,
        ]);
    }
}
