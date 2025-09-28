<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CheatEvent;
use App\Models\Grade;
use App\Models\ExamGroup;
use Carbon\Carbon;

class AntiCheatController extends Controller
{
    /**
     * Record a cheat event and enforce thresholds.
     */
    public function report(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|integer',
            'exam_session_id' => 'required|integer',
            'grade_id' => 'required|integer',
            'type' => 'required|string|max:64',
            'meta' => 'nullable',
        ]);

        $student = auth()->guard('student')->user();
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        // If already locked, just inform client
        if ($student->is_locked) {
            $examGroup = ExamGroup::where('exam_id', $request->exam_id)
                ->where('exam_session_id', $request->exam_session_id)
                ->where('student_id', $student->id)
                ->first();
            return response()->json([
                'success' => true,
                'locked' => true,
                'message' => 'Akun telah dikunci.',
                'redirect_to' => $examGroup ? route('student.exams.resultExam', $examGroup->id) : route('student.dashboard'),
            ]);
        }

        $grade = Grade::where('id', $request->grade_id)
            ->where('exam_id', $request->exam_id)
            ->where('exam_session_id', $request->exam_session_id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        // Log event
        CheatEvent::create([
            'student_id' => $student->id,
            'exam_id' => $request->exam_id,
            'exam_session_id' => $request->exam_session_id,
            'grade_id' => $grade->id,
            'type' => $request->type,
            'meta' => $request->meta ?? null,
        ]);

        // Increment and update status
        $grade->cheat_count = ($grade->cheat_count ?? 0) + 1;
        $grade->last_cheat_at = Carbon::now();

        $threshold = 3; // allowed warnings

        if ($grade->cheat_count <= $threshold) {
            $grade->cheat_status = 'WARNED';
            $grade->save();
            return response()->json([
                'success' => true,
                'locked' => false,
                'cheat_count' => $grade->cheat_count,
                'remaining' => max(0, $threshold - $grade->cheat_count),
                'message' => 'Peringatan kecurangan #' . $grade->cheat_count,
            ]);
        }

        // Exceeded -> lock account and finish exam with 0
        $grade->cheat_status = 'LOCKED';
        $grade->cheat_reason = 'Melebihi batas kecurangan (' . $grade->cheat_count . ')';
        $grade->end_time = Carbon::now();
        $grade->total_correct = 0;
        $grade->grade = 0;
        $grade->save();

        $student->is_locked = true;
        $student->locked_at = Carbon::now();
        $student->save();

        $examGroup = ExamGroup::where('exam_id', $request->exam_id)
            ->where('exam_session_id', $request->exam_session_id)
            ->where('student_id', $student->id)
            ->first();

        return response()->json([
            'success' => true,
            'locked' => true,
            'cheat_count' => $grade->cheat_count,
            'message' => 'Akun dikunci dan ujian diakhiri dengan nilai 0.',
            'redirect_to' => $examGroup ? route('student.exams.resultExam', $examGroup->id) : route('student.dashboard'),
        ]);
    }
}
