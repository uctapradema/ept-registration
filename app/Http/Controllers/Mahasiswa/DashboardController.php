<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $activeRegistration = Registration::with('examSchedule')
            ->where('user_id', $user->id)
            ->active()
            ->latest()
            ->first();

        $recentRegistrations = Registration::with('examSchedule')
            ->where('user_id', $user->id)
            ->whereNotIn('status', ['pending_payment', 'awaiting_verification', 'verified'])
            ->latest()
            ->take(5)
            ->get();

        return view('mahasiswa.dashboard', compact('activeRegistration', 'recentRegistrations'));
    }
}
