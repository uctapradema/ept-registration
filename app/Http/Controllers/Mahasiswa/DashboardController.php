<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Enums\RegistrationStatus;
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
            ->history()
            ->latest()
            ->take(5)
            ->get();

        return view('mahasiswa.dashboard', compact('activeRegistration', 'recentRegistrations'));
    }
}
