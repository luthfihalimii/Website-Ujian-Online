<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CheatEvent;
use App\Models\Grade;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheatMonitorController extends Controller
{
    public function index()
    {
        $cheatEvents = CheatEvent::with(['student.classroom', 'exam', 'exam_session'])
            ->latest()
            ->paginate(15);

        $lockedStudents = Student::where('is_locked', true)
            ->with('classroom')
            ->latest('locked_at')
            ->paginate(15, ['*'], 'locked_page');

        $summary = [
            'total_events' => CheatEvent::count(),
            'today_events' => CheatEvent::whereDate('created_at', now()->toDateString())->count(),
            'locked_students' => Student::where('is_locked', true)->count(),
            'warned_attempts' => Grade::where('cheat_status', 'WARNED')->count(),
            'locked_attempts' => Grade::where('cheat_status', 'LOCKED')->count(),
        ];

        $typeBreakdown = CheatEvent::select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        return inertia('Admin/Cheat/Index', [
            'events' => $cheatEvents,
            'locked_students' => $lockedStudents,
            'summary' => $summary,
            'type_breakdown' => $typeBreakdown,
        ]);
    }

    public function unlock(Student $student)
    {
        $student->is_locked = false;
        $student->locked_at = null;
        $student->save();

        return redirect()->back();
    }
}
