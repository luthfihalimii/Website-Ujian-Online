<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\ExamSession;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Exports\GradesExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{    
    /**
     * index
     *
     * @return void
     */
    public function index()
    {
        $exams = Exam::with('lesson', 'classroom')->get();
        $sessions = ExamSession::select('id', 'exam_id', 'title', 'start_time', 'end_time')
            ->orderBy('start_time')
            ->get();

        return inertia('Admin/Reports/Index', [
            'exams'   => $exams,
            'sessions'=> $sessions,
            'grades'  => [],
            'filters' => [
                'exam_id' => null,
                'exam_session_id' => null,
            ],
            'summary' => $this->emptySummary(),
        ]);
    }
    
    /**
     * filter
     *
     * @param  mixed $request
     * @return void
     */
    public function filter(Request $request)
    {
        $validated = $request->validate([
            'exam_id' => 'required|integer|exists:exams,id',
            'exam_session_id' => 'nullable|integer',
        ]);

        $exams = Exam::with('lesson', 'classroom')->get();
        $sessions = ExamSession::select('id', 'exam_id', 'title', 'start_time', 'end_time')
            ->orderBy('start_time')
            ->get();

        $exam = $exams->firstWhere('id', (int) $validated['exam_id']);

        $availableSessions = $sessions->where('exam_id', $exam->id);

        $selectedSessionId = $validated['exam_session_id'] ?? null;
        if ($selectedSessionId && !$availableSessions->contains('id', (int) $selectedSessionId)) {
            $selectedSessionId = null;
        }

        if (!$selectedSessionId && $availableSessions->isNotEmpty()) {
            $selectedSessionId = $availableSessions->first()->id;
        }

        $grades = collect();

        if ($selectedSessionId) {
            $grades = Grade::with('student', 'exam.classroom', 'exam.lesson', 'exam_session')
                ->where('exam_id', $exam->id)
                ->where('exam_session_id', $selectedSessionId)
                ->get();
        }

        return inertia('Admin/Reports/Index', [
            'exams'   => $exams,
            'sessions'=> $sessions,
            'grades'  => $grades,
            'filters' => [
                'exam_id' => $exam->id,
                'exam_session_id' => $selectedSessionId,
            ],
            'summary' => $this->buildSummary($grades),
        ]);
        
    }

    /**
     * export
     *
     * @param  mixed $request
     * @return void
     */
    public function export(Request $request)
    {
        $validated = $request->validate([
            'exam_id' => 'required|integer|exists:exams,id',
            'exam_session_id' => 'nullable|integer|exists:exam_sessions,id',
        ]);

        $exam = Exam::with('lesson', 'classroom')->findOrFail($validated['exam_id']);

        $examSessionQuery = ExamSession::where('exam_id', $exam->id);

        if (!empty($validated['exam_session_id'])) {
            $examSessionQuery->where('id', $validated['exam_session_id']);
        }

        $exam_session = $examSessionQuery->orderBy('start_time')->firstOrFail();

        $grades = Grade::with('student', 'exam.classroom', 'exam.lesson', 'exam_session')
            ->where('exam_id', $exam->id)
            ->where('exam_session_id', $exam_session->id)
            ->get();

        $filename = sprintf(
            'grade_%s_%s_%s.xlsx',
            str_replace(' ', '_', strtolower($exam->title)),
            str_replace(' ', '_', strtolower($exam_session->title)),
            Carbon::now()->format('Ymd_His')
        );

        return Excel::download(new GradesExport($grades), $filename);
    }

    protected function emptySummary(): array
    {
        return [
            'participants' => 0,
            'avg_grade' => 0,
            'completed' => 0,
            'cheat_warnings' => 0,
            'cheat_locked' => 0,
        ];
    }

    protected function buildSummary($grades): array
    {
        if ($grades instanceof Collection === false) {
            $grades = collect($grades);
        }

        if ($grades->isEmpty()) {
            return $this->emptySummary();
        }

        $participants = $grades->count();
        $avgGrade = round((float) $grades->avg('grade'), 2);

        return [
            'participants' => $participants,
            'avg_grade' => $avgGrade,
            'completed' => $grades->whereNotNull('end_time')->count(),
            'cheat_warnings' => $grades->where('cheat_status', 'WARNED')->count(),
            'cheat_locked' => $grades->where('cheat_status', 'LOCKED')->count(),
        ];
    }
}
