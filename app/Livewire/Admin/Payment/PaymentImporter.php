<?php

namespace App\Livewire\Admin\Payment;

use App\Enums\Book\SalesPlatformEnum;
use App\Imports\FidiboImporter;
use App\Imports\KetabrahImporter;
use App\Imports\NavarImporter;
use App\Imports\NovinKetabImporter;
use App\Imports\TaghchehImporter;
use App\Models\ImportLog;
use Exception;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class PaymentImporter extends Component
{
    use WithFileUploads;
    use WithPagination;

    public $file;
    public $platform;
    public $showModal = false;
    public ?ImportLog $selectedLog = null;

    // Filter properties
    public $filterPlatform = '';
    public $filterStatus = '';

    protected $rules = [
        'platform' => 'required|in:1,2,3,4,5',
        'file' => 'required|mimes:xlsx,csv,xls|max:10240',
    ];

    protected $messages = [
        'platform.required' => 'انتخاب پلتفرم فروش الزامی است.',
        'platform.in' => 'پلتفرم انتخاب شده معتبر نیست.',
        'file.required' => 'لطفا یک فایل برای ایمپورت انتخاب کنید.',
        'file.mimes' => 'فایل انتخابی باید با فرمت xlsx, csv یا xls باشد.',
        'file.max' => 'حداکثر حجم فایل می‌تواند ۱۰ مگابایت باشد.',
    ];

    /**
     * Handles the file import process directly without queuing.
     */
    public function import()
    {
        $this->validate();

        $path = $this->file->store('imports', 'local');

        $log = ImportLog::create([
            'user_id' => Auth::id(),
            'platform' => $this->platform,
            'file_path' => $path,
            'status' => 'processing',
        ]);

        try {
            $importer = $this->getImporter($log);

            // Import the file directly (synchronously)
            Excel::import($importer, $path, 'local');

            // The log status will be updated by the importer itself upon completion

            $this->dispatch('toast', text: 'فایل با موفقیت پردازش و وارد شد.', icon: 'success');

        } catch (\Throwable $e) {
            // If any error occurs during the import, log it
            $log->status = 'failed';
            $log->details = ['error' => 'خطای کلی در پردازش فایل: ' . $e->getMessage()];
            $log->save();

            $this->dispatch('toast', text: 'در هنگام پردازش فایل خطایی رخ داد.', icon: 'error');
        }

        $this->reset(['file', 'platform']);
    }

    /**
     * Returns the correct importer class based on the selected platform.
     * @throws Exception
     */
    private function getImporter(ImportLog $log)
    {
        $platformEnum = SalesPlatformEnum::from((int)$this->platform);

        switch ($platformEnum) {
            case SalesPlatformEnum::FIDIBO:
                return new FidiboImporter($log);

            case SalesPlatformEnum::NAVAR:
                return new NavarImporter($log);

            case SalesPlatformEnum::KETABRAH:
                return new KetabrahImporter($log);

            case SalesPlatformEnum::TAGHCHEH:
                return new TaghchehImporter($log);

            case SalesPlatformEnum::NOVIN_KETAB:
                return new NovinKetabImporter($log);

            default:
                throw new \Exception("ایمپورتر برای پلتفرم انتخاب شده یافت نشد.");
        }
    }


    public function showLogDetails(ImportLog $log)
    {
        $this->selectedLog = $log;
        $this->showModal = true;
    }

    public function render()
    {
        $query = ImportLog::with('user')->select([ // <-- Select only the columns needed for the list view
            'id',
            'user_id',
            'platform',
            'status',
            'new_records',
            'updated_records',
            'failed_records',
            'created_at',
        ])
            ->latest();

        if ($this->filterPlatform) {
            $query->where('platform', $this->filterPlatform);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        return view('livewire.admin.payment.payment-importer', [
            'platforms' => SalesPlatformEnum::cases(),
            'logs' => $query->paginate(10),
        ]);
    }
}
