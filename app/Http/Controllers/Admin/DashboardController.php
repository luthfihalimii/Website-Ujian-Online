<?php

namespace App\Http\Controllers\Admin;

use App\Models\Exam;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\ExamSession;
use App\Models\CheatEvent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
    * @return mixed
     */
    public function __invoke(Request $request)
    {
        //count students
        $students = Student::count();

        //count exams
        $exams = Exam::count();

        //count exam sessions
        $exam_sessions = ExamSession::count();

        //count classrooms
        $classrooms = Classroom::count();

        // cheating metrics (counts only)
        $locked_students_count = Student::where('is_locked', true)->count();
        $cheat_events_count = CheatEvent::count();

        return inertia('Admin/Dashboard/Index', [
            'students'      => $students,
            'exams'         => $exams,
            'exam_sessions' => $exam_sessions,
            'classrooms'    => $classrooms,
            'locked_students_count' => $locked_students_count,
            'cheat_events_count'    => $cheat_events_count,
        ]);
    }
}
