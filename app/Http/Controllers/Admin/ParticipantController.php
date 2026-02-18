<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamSchedule;
use App\Models\Registration;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    public function index(Request $request)
    {
        $examScheduleId = $request->get('exam_schedule_id');
        
        $examSchedules = ExamSchedule::where('is_active', true)
            ->orderBy('exam_date', 'desc')
            ->get();

        $query = Registration::with(['user', 'examSchedule'])
            ->where('status', 'verified')
            ->orderBy('registration_number', 'asc');

        if ($examScheduleId) {
            $query->where('exam_schedule_id', $examScheduleId);
        }

        $registrations = $query->get()->groupBy('exam_schedule_id');

        return view('admin.participants.index', compact('registrations', 'examSchedules', 'examScheduleId'));
    }

    public function print(Request $request)
    {
        $examScheduleId = $request->get('exam_schedule_id');
        
        $query = Registration::with(['user', 'examSchedule'])
            ->where('status', 'verified')
            ->orderBy('registration_number', 'asc');

        if ($examScheduleId) {
            $query->where('exam_schedule_id', $examScheduleId);
        }

        $registrations = $query->get()->groupBy('exam_schedule_id');

        return view('admin.participants.print', compact('registrations', 'examScheduleId'));
    }
}
