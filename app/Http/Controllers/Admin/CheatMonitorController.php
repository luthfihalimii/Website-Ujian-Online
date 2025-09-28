<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CheatEvent;
use App\Models\Student;
use Illuminate\Http\Request;

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

        return inertia('Admin/Cheat/Index', [
            'events' => $cheatEvents,
            'locked_students' => $lockedStudents,
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
