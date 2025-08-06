<?php

namespace App\Livewire\Admin\Dashboard;

use App\Models\User;
use Livewire\Component;

class DashboardIndex extends Component
{
    public function render()
    {
        $user = auth()->user();
        return view('livewire.admin.dashboard.dashboard-index', compact('user'));
    }
}
