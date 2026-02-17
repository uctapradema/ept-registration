<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\ExamSchedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index()
    {
        $schedules = ExamSchedule::where('is_active', true)
            ->where('registration_deadline', '>', now())
            ->orderBy('exam_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        return view('mahasiswa.schedules.index', compact('schedules'));
    }
}
