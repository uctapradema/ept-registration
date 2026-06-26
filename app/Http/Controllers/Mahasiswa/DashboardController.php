<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Constants\AppConstants;
use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $activeRegistration = Registration::with('examSchedule')
            ->forUser($user->id)
            ->active()
            ->latest()
            ->first();

        $recentRegistrations = Registration::with('examSchedule')
            ->forUser($user->id)
            ->history()
            ->latest()
            ->take(AppConstants::DASHBOARD_RECENT_LIMIT)
            ->get();

        return view('mahasiswa.dashboard', compact('activeRegistration', 'recentRegistrations'));
    }
}
