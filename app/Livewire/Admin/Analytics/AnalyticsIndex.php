<?php

namespace App\Livewire\Admin\Analytics;

use App\Lib\Analytics\AnalyticsService;
use Livewire\Component;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;
use App\Enums\Book\SalesPlatformEnum; // <-- این خط را اضافه کنید

class AnalyticsIndex extends Component
{
    use WithPagination;

    public ?string $startDate = '';
    public ?string $endDate = '';
    public bool $chartsLoaded = false; // یک فلگ برای جلوگیری از اجرای چندباره

    // (جدید) فیلترهای اختیاری
    public ?int $book_id = null;
    public string $platform = '';

    public function mount()
    {
        $this->endDate = Jalalian::now()->format('Y/m/d');
        $this->startDate = Jalalian::now()->subDays(30)->format('Y/m/d');
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
        // (تغییر) book_id و platform به شرط اضافه شدند
        if (in_array($property, ['startDate', 'endDate', 'book_id', 'platform'])) {
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
        $startDateCarbon = Jalalian::fromFormat('Y/m/d', $this->startDate)->toCarbon()->startOfDay();
        $endDateCarbon = Jalalian::fromFormat('Y/m/d', $this->endDate)->toCarbon()->endOfDay();

        // (تغییر) فیلترهای جدید به سرویس ارسال می‌شوند
        $analyticsService = new AnalyticsService($startDateCarbon, $endDateCarbon, $this->book_id, $this->platform);

        $comprehensiveStats = $analyticsService->getComprehensiveStats();
        $recentPayments = $analyticsService->getRecentPayments(20);
        $topAuthors = $analyticsService->getTopAuthors(20);
        $topPublishers = $analyticsService->getTopPublishers(20);
        $topNarrators = $analyticsService->getTopNarrators(20);


        return view('livewire.admin.analytics.analytics-index', [
            'stats' => $comprehensiveStats,
            'recentPayments' => $recentPayments,
            'topAuthors' => $topAuthors,
            'topPublishers' => $topPublishers,
            'topNarrators' => $topNarrators,
            'platforms' => SalesPlatformEnum::cases(),
        ]);
    }

    /**
     * یک متد خصوصی برای جلوگیری از تکرار کد (DRY)
     * این متد وظیفه گرفتن داده‌های نمودار و ارسال آن به فرانت‌اند را دارد.
     */
    private function dispatchChartData(): void
    {
        $startDateCarbon = Jalalian::fromFormat('Y/m/d', $this->startDate)->toCarbon()->startOfDay();
        $endDateCarbon = Jalalian::fromFormat('Y/m/d', $this->endDate)->toCarbon()->endOfDay();

        // (تغییر) فیلترهای جدید به سرویس ارسال می‌شوند
        $analyticsService = new AnalyticsService($startDateCarbon, $endDateCarbon, $this->book_id, $this->platform);

        $allChartData = $analyticsService->getAllChartData();
        $this->dispatch('updateAllCharts', chartsData: $allChartData);
    }
}
