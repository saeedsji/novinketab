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
    public bool $chartsLoaded = false; // یک فلگ برای جلوگیری از اجرای چندباره

    public function mount()
    {
        $this->endDate = Carbon::now()->toDateString();
        $this->startDate = Carbon::now()->subDays(30)->toDateString();
    }

    /**
     * این متد توسط wire:init در فایل view فراخوانی می‌شود.
     * این کار تضمین می‌کند که داده‌های نمودار فقط زمانی ارسال می‌شوند که فرانت‌اند آماده دریافت باشد.
     */
    public function loadCharts()
    {
        // فقط یک بار داده‌ها را بارگذاری و ارسال کن
        if ($this->chartsLoaded) {
            return;
        }

        $this->dispatchChartData();
        $this->chartsLoaded = true;
    }

    /**
     * این هوک زمانی اجرا می‌شود که یکی از پراپرتی‌های startDate یا endDate تغییر کند.
     */
    public function updated($property)
    {
        if (in_array($property, ['startDate', 'endDate'])) {
            $this->resetPage();
            // پس از تغییر تاریخ، داده‌های جدید نمودار را ارسال کن
            $this->dispatchChartData();
        }
    }

    /**
     * منطق اصلی را از متد render خارج می‌کنیم.
     * render فقط باید مسئول نمایش view باشد.
     */
    public function render()
    {
        $analyticsService = new AnalyticsService($this->startDate, $this->endDate);

        $comprehensiveStats = $analyticsService->getComprehensiveStats();
        $recentPayments = $analyticsService->getRecentPayments(20);
        $topAuthors = $analyticsService->getTopAuthors(20);


        return view('livewire.admin.analytics.analytics-index', [
            'stats' => $comprehensiveStats,
            'recentPayments' => $recentPayments,
            'topAuthors' => $topAuthors,
        ]);
    }

    /**
     * یک متد خصوصی برای جلوگیری از تکرار کد (DRY)
     * این متد وظیفه گرفتن داده‌های نمودار و ارسال آن به فرانت‌اند را دارد.
     */
    private function dispatchChartData(): void
    {
        $analyticsService = new AnalyticsService($this->startDate, $this->endDate);
        $allChartData = $analyticsService->getAllChartData();
        $this->dispatch('updateAllCharts', chartsData: $allChartData);
    }
}
