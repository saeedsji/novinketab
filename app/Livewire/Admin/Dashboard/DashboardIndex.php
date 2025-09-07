<?php

namespace App\Livewire\Admin\Dashboard;

use App\Models\Book; // فرض می‌کنیم مدل کتاب وجود دارد
use App\Models\ImportLog;
use App\Models\Payment; // فرض می‌کنیم مدل پرداخت وجود دارد
use App\Models\User;
use Livewire\Component;

class DashboardIndex extends Component
{
    public function render()
    {
        /** @var User $user */
        $user = auth()->user();

        // واکشی اولین نقش کاربر
        $userRole = $user->getRoleNames()->isNotEmpty() ? $user->getRoleNames()->first() : null;

        // بخش آمار کلیدی
        $stats = [
            'total_books' => Book::count(),
            'payments_this_month' => Payment::where('sale_date', '>=', now()->startOfMonth())->count(),
            'successful_imports' => ImportLog::where('status', 'completed')->count(),
        ];

        // بخش فعالیت‌های اخیر (۵ مورد آخر)
        $recentImports = ImportLog::with('user')
            ->select([
                'id',
                'user_id',
                'platform',
                'status',
                'created_at',
            ])
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.admin.dashboard.dashboard-index', [
            'user' => $user,
            'userRole' => $userRole,
            'stats' => $stats,
            'recentImports' => $recentImports,
        ]);
    }
}
