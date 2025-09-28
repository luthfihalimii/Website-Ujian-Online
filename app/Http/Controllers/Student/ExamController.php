<?php

namespace App\Http\Controllers\Student;

use Carbon\Carbon;
use App\Models\Grade;
use App\Models\Answer;
use App\Models\CheatEvent;
use App\Models\Question;
use App\Models\ExamGroup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ExamController extends Controller
{
        /**
     * confirmation
     *
     * @param  mixed $id
     * @return void
     */
    public function confirmation($id)
    {
        //get exam group
        $exam_group = ExamGroup::with('exam.lesson', 'exam_session', 'student.classroom')
                    ->where('student_id', auth()->guard('student')->user()->id)
                    ->where('id', $id)
                    ->first();

        //get grade / nilai
        $grade = Grade::where('exam_id', $exam_group->exam->id)
                    ->where('exam_session_id', $exam_group->exam_session->id)
                    ->where('student_id', auth()->guard('student')->user()->id)
                    ->first();
        
        //return with inertia
        return inertia('Student/Exams/Confirmation', [
            'exam_group' => $exam_group,
            'grade' => $grade,
        ]);
    }

    /**
     * startExam
     *
     * @param  mixed $id
     * @return void
     */
    public function startExam($id)
    {
        $studentId = auth()->guard('student')->id();

        // get exam group
        $exam_group = ExamGroup::with('exam.lesson', 'exam_session', 'student.classroom')
            ->where('student_id', $studentId)
            ->where('id', $id)
            ->first();

        if (!$exam_group) {
            return redirect()->route('student.dashboard');
        }

        // get grade / nilai
        $grade = Grade::where('exam_id', $exam_group->exam->id)
            ->where('exam_session_id', $exam_group->exam_session->id)
            ->where('student_id', $studentId)
            ->firstOrFail();

        if ($grade->end_time) {
            return redirect()->route('student.exams.resultExam', $exam_group->id);
        }

        $windowState = $this->examWindowState($exam_group);

        if ($windowState === 'before') {
            return redirect()->route('student.exams.confirmation', $exam_group->id)
                ->with('error', 'Ujian belum dimulai.');
        }

        if ($windowState === 'after') {
            $this->finalizeGrade($grade, $exam_group);
            return redirect()->route('student.exams.resultExam', $exam_group->id)
                ->with('error', 'Waktu ujian telah berakhir.');
        }

        if (!$grade->start_time) {
            $grade->start_time = Carbon::now();
            $grade->duration = $exam_group->exam->duration * 60000;
            $grade->save();
        }

        $this->syncRemainingDuration($grade->refresh(), $exam_group);

        //cek apakah questions / soal ujian di random
        if ($exam_group->exam->random_question == 'Y') {

            //get questions / soal ujian
            $questions = Question::where('exam_id', $exam_group->exam->id)->inRandomOrder()->get();

        } else {

            //get questions / soal ujian
            $questions = Question::where('exam_id', $exam_group->exam->id)->get();

        }

        //define pilihan jawaban default
        $question_order = 1;

        foreach ($questions as $question) {

            //buat array jawaban / answer
            $options = [1,2];
            if(!empty($question->option_3)) $options[] = 3;
            if(!empty($question->option_4)) $options[] = 4;
            if(!empty($question->option_5)) $options[] = 5;

            //acak jawaban / answer
            if($exam_group->exam->random_answer == 'Y') {
                shuffle($options);
            }

            //cek apakah sudah ada data jawaban
            $answer = Answer::where('student_id', $studentId)
                    ->where('exam_id', $exam_group->exam->id)
                    ->where('exam_session_id', $exam_group->exam_session->id)
                    ->where('question_id', $question->id)
                    ->first();

            //jika sudah ada jawaban / answer
            if($answer) {

                //update urutan question / soal
                $answer->question_order = $question_order;
                $answer->update();

            } else {

                //buat jawaban default baru
                Answer::create([
                    'exam_id'           => $exam_group->exam->id,
                    'exam_session_id'   => $exam_group->exam_session->id,
                    'question_id'       => $question->id,
                    'student_id'        => $studentId,
                    'question_order'    => $question_order,
                    'answer_order'      => implode(",", $options),
                    'answer'            => 0,
                    'is_correct'        => 'N'
                ]);

            }
            $question_order++;

        }

        //redirect ke ujian halaman 1
        return redirect()->route('student.exams.show', [
            'id'    => $exam_group->id, 
            'page'  => 1
        ]);   
    }
    
    /**
     * show
     *
     * @param  mixed $id
     * @param  mixed $page
     * @return void
     */
    public function show($id, $page)
    {
        $studentId = auth()->guard('student')->id();

        //get exam group
        $exam_group = ExamGroup::with('exam.lesson', 'exam_session', 'student.classroom')
                    ->where('student_id', $studentId)
                    ->where('id', $id)
                    ->first();

        if(!$exam_group) {
            return redirect()->route('student.dashboard');
        }

        $grade = Grade::where('exam_id', $exam_group->exam->id)
                ->where('exam_session_id', $exam_group->exam_session->id)
                ->where('student_id', $studentId)
                ->first();

        if(!$grade) {
            return redirect()->route('student.dashboard');
        }

        if($grade->end_time) {
            return redirect()->route('student.exams.resultExam', $exam_group->id);
        }

        $windowState = $this->examWindowState($exam_group);

        if ($windowState === 'before') {
            return redirect()->route('student.exams.confirmation', $exam_group->id)
                ->with('error', 'Ujian belum dimulai.');
        }

        if ($windowState === 'after') {
            $this->finalizeGrade($grade, $exam_group);
            return redirect()->route('student.exams.resultExam', $exam_group->id)
                ->with('error', 'Waktu ujian telah berakhir.');
        }

        $remaining = $this->syncRemainingDuration($grade, $exam_group);

        if ($remaining <= 0) {
            $this->finalizeGrade($grade->refresh(), $exam_group);
            return redirect()->route('student.exams.resultExam', $exam_group->id)
                ->with('error', 'Durasi ujian telah habis.');
        }

        //get all questions
        $all_questions = Answer::with('question')
                        ->where('student_id', $studentId)
                        ->where('exam_id', $exam_group->exam->id)
                        ->orderBy('question_order', 'ASC')
                        ->get();

        //count all question answered
        $question_answered = Answer::with('question')
                        ->where('student_id', $studentId)
                        ->where('exam_id', $exam_group->exam->id)
                        ->where('answer', '!=', 0)
                        ->count();


        //get question active
        $question_active = Answer::with('question.exam')
                        ->where('student_id', $studentId)
                        ->where('exam_id', $exam_group->exam->id)
                        ->where('question_order', $page)
                        ->first();
        
        //explode atau pecah jawaban
        if ($question_active) {
            $answer_order = explode(",", $question_active->answer_order);
        } else  {
            $answer_order = [];
        }

        //get duration (updated)
        $duration = $grade->refresh();

        //return with inertia
        return inertia('Student/Exams/Show', [
            'id'                => (int) $id,
            'page'              => (int) $page,
            'exam_group'        => $exam_group,
            'all_questions'     => $all_questions,
            'question_answered' => $question_answered,
            'question_active'   => $question_active,
            'answer_order'      => $answer_order,
            'duration'          => $duration,
        ]); 
    }

        /**
     * updateDuration
     *
     * @param  mixed $request
     * @param  mixed $grade_id
     * @return void
     */
    public function updateDuration(Request $request, $grade_id)
    {
        $studentId = auth()->guard('student')->id();

        $grade = Grade::where('id', $grade_id)
            ->where('student_id', $studentId)
            ->firstOrFail();

        $exam_group = $this->resolveExamGroup($grade);

        if ($grade->end_time) {
            $redirectTo = $exam_group ? route('student.exams.resultExam', $exam_group->id) : route('student.dashboard');

            return response()->json([
                'success' => false,
                'message' => 'Ujian telah berakhir.',
                'redirect_to' => $redirectTo,
                'code' => 423,
            ]);
        }

        if (!$exam_group) {
            return response()->json([
                'success' => false,
                'message' => 'Context ujian tidak ditemukan.',
                'code' => 404,
            ]);
        }

        $deviceToken = $request->input('device_token');
        $deviceInfo = (array) $request->input('device_info', []);

        if ($violation = $this->ensureSingleDevice($grade, $deviceToken, $deviceInfo)) {
            return response()->json([
                'success' => false,
                'message' => $violation['message'],
                'redirect_to' => $violation['redirect_to'],
                'code' => 423,
            ], 423);
        }

        $windowState = $this->examWindowState($exam_group);

        if ($windowState === 'before') {
            return response()->json([
                'success' => false,
                'message' => 'Ujian belum dimulai.',
                'code' => 423,
            ]);
        }

        if ($windowState === 'after') {
            $this->finalizeGrade($grade, $exam_group);
            return response()->json([
                'success' => false,
                'message' => 'Waktu ujian telah berakhir.',
                'redirect_to' => route('student.exams.resultExam', $exam_group->id),
                'code' => 423,
            ]);
        }

        $remaining = $this->syncRemainingDuration($grade, $exam_group);

        if ($remaining <= 0) {
            $this->finalizeGrade($grade->refresh(), $exam_group);
            return response()->json([
                'success' => false,
                'message' => 'Durasi ujian telah habis.',
                'redirect_to' => route('student.exams.resultExam', $exam_group->id),
                'code' => 423,
            ]);
        }

        return response()->json([
            'success'  => true,
            'message' => 'Duration updated successfully.',
            'remaining' => $remaining,
        ]);
    }

    /**
     * answerQuestion
     *
     * @param  mixed $request
     * @return void
     */
    public function answerQuestion(Request $request)
    {
        $studentId = auth()->guard('student')->id();

        //update duration
        $grade = Grade::where('exam_id', $request->exam_id)
                ->where('exam_session_id', $request->exam_session_id)
                ->where('student_id', $studentId)
                ->first();

        if(!$grade) {
            return redirect()->route('student.dashboard');
        }

        if($grade->end_time) {
            $exam_group = $this->resolveExamGroup($grade);
            if ($exam_group) {
                return redirect()->route('student.exams.resultExam', $exam_group->id);
            }

            return redirect()->route('student.dashboard');
        }

        $exam_group = $this->resolveExamGroup($grade);

        if(!$exam_group) {
            return redirect()->route('student.dashboard');
        }

        $deviceToken = $request->input('device_token');
        $deviceInfo = (array) $request->input('device_info', []);

        if ($violation = $this->ensureSingleDevice($grade, $deviceToken, $deviceInfo)) {
            return redirect($violation['redirect_to'] ?? route('student.dashboard'))
                ->with('error', $violation['message']);
        }

        $windowState = $this->examWindowState($exam_group);

        if ($windowState !== 'active') {
            $this->finalizeGrade($grade, $exam_group);
            return redirect()->route('student.exams.resultExam', $exam_group->id)
                ->with('error', 'Sesi ujian tidak tersedia.');
        }

        $remaining = $this->syncRemainingDuration($grade, $exam_group);

        if ($remaining <= 0) {
            $this->finalizeGrade($grade->refresh(), $exam_group);
            return redirect()->route('student.exams.resultExam', $exam_group->id)
                ->with('error', 'Durasi ujian telah habis.');
        }

        //get question
        $question = Question::find($request->question_id);
        
        if(!$question) {
            return redirect()->back();
        }

        //cek apakah jawaban sudah benar
        $result = ($question->answer == $request->answer) ? 'Y' : 'N';

        //get answer
        $answer   = Answer::where('exam_id', $request->exam_id)
                    ->where('exam_session_id', $request->exam_session_id)
                    ->where('student_id', $studentId)
                    ->where('question_id', $request->question_id)
                    ->first();

        //update jawaban
        if($answer) {
            $answer->answer     = $request->answer;
            $answer->is_correct = $result;
            $answer->update();
        }

        return redirect()->back();
    }

    /**
     * endExam
     *
     * @param  mixed $request
     * @return void
     */
    public function endExam(Request $request)
    {
        $studentId = auth()->guard('student')->id();

        $exam_group = ExamGroup::with('exam.lesson', 'exam_session', 'student.classroom')
            ->where('student_id', $studentId)
            ->where('id', $request->exam_group_id)
            ->first();

        if (!$exam_group) {
            return redirect()->route('student.dashboard');
        }

        $grade = Grade::where('exam_id', $request->exam_id)
            ->where('exam_session_id', $request->exam_session_id)
            ->where('student_id', $studentId)
            ->first();

        if (!$grade) {
            return redirect()->route('student.dashboard');
        }

        if ($grade->end_time) {
            return redirect()->route('student.exams.resultExam', $exam_group->id);
        }

        $deviceToken = $request->input('device_token');
        $deviceInfo = (array) $request->input('device_info', []);

        if ($violation = $this->ensureSingleDevice($grade, $deviceToken, $deviceInfo)) {
            return redirect($violation['redirect_to'] ?? route('student.dashboard'))
                ->with('error', $violation['message']);
        }

        $this->syncRemainingDuration($grade, $exam_group);
        $this->finalizeGrade($grade->refresh(), $exam_group);

        //redirect hasil
        return redirect()->route('student.exams.resultExam', $exam_group->id);
    }

    /**
     * resultExam
     *
     * @param  mixed $id
     * @return void
     */
    public function resultExam($exam_group_id)
    {
        //get exam group
        $exam_group = ExamGroup::with('exam.lesson', 'exam_session', 'student.classroom')
                ->where('student_id', auth()->guard('student')->user()->id)
                ->where('id', $exam_group_id)
                ->first();

        if (!$exam_group) {
            return redirect()->route('student.dashboard');
        }

        //get grade / nilai
        $grade = Grade::where('exam_id', $exam_group->exam->id)
                ->where('exam_session_id', $exam_group->exam_session->id)
                ->where('student_id', auth()->guard('student')->user()->id)
                ->first();

        if ($grade && !$grade->end_time) {
            $this->syncRemainingDuration($grade, $exam_group);
            $this->finalizeGrade($grade->refresh(), $exam_group);
            $grade = $grade->refresh();
        }

        //return with inertia
        return inertia('Student/Exams/Result', [
            'exam_group' => $exam_group,
            'grade'      => $grade,
        ]);
    }

    /**
     * Determine current exam window state for a session.
     */
    protected function examWindowState(ExamGroup $exam_group): string
    {
        $now = Carbon::now();
        $start = Carbon::parse($exam_group->exam_session->start_time);
        $end = Carbon::parse($exam_group->exam_session->end_time);

        if ($now->lt($start)) {
            return 'before';
        }

        if ($now->gt($end)) {
            return 'after';
        }

        return 'active';
    }

    /**
     * Sync remaining duration based on real time and session window.
     */
    protected function syncRemainingDuration(Grade $grade, ExamGroup $exam_group): int
    {
        $maxDurationMs = max(0, (int) $exam_group->exam->duration * 60000);
        $now = Carbon::now();

        if (!$grade->start_time) {
            if ((int) $grade->duration !== $maxDurationMs) {
                $grade->duration = $maxDurationMs;
                $grade->save();
            }

            return $maxDurationMs;
        }

        $startTime = Carbon::parse($grade->start_time);
        $elapsedSeconds = $startTime->diffInSeconds($now);
        $remaining = max(0, $maxDurationMs - ($elapsedSeconds * 1000));

        $sessionEnd = Carbon::parse($exam_group->exam_session->end_time);
        if ($now->gt($sessionEnd)) {
            $remaining = 0;
        } else {
            $sessionRemaining = max(0, $now->diffInSeconds($sessionEnd) * 1000);
            $remaining = min($remaining, $sessionRemaining);
        }

        if ((int) $grade->duration !== $remaining) {
            $grade->duration = $remaining;
            $grade->save();
        }

        return $remaining;
    }

    /**
     * Finalize grade calculation and close the exam attempt.
     */
    protected function finalizeGrade(Grade $grade, ExamGroup $exam_group, bool $forceZero = false): void
    {
        if ($grade->end_time) {
            return;
        }

        if ($forceZero) {
            $totalCorrect = 0;
            $score = 0;
        } else {
            $totalCorrect = Answer::where('exam_id', $grade->exam_id)
                ->where('exam_session_id', $grade->exam_session_id)
                ->where('student_id', $grade->student_id)
                ->where('is_correct', 'Y')
                ->count();

            $questionCount = Question::where('exam_id', $grade->exam_id)->count();
            $score = $questionCount > 0 ? round($totalCorrect / $questionCount * 100, 2) : 0;
        }

        $grade->total_correct = $forceZero ? 0 : $totalCorrect;
        $grade->grade = $forceZero ? 0 : $score;
        $grade->end_time = Carbon::now();
        $grade->duration = 0;
        $grade->save();
    }

    /**
     * Resolve the exam group associated with a grade.
     */
    protected function resolveExamGroup(Grade $grade): ?ExamGroup
    {
        return ExamGroup::with('exam.lesson', 'exam_session', 'student.classroom')
            ->where('exam_id', $grade->exam_id)
            ->where('exam_session_id', $grade->exam_session_id)
            ->where('student_id', $grade->student_id)
            ->first();
    }

    /**
     * Resolve exam group ID for convenience.
     */
    protected function resolveExamGroupId(Grade $grade): ?int
    {
        return optional($this->resolveExamGroup($grade))->id;
    }

    /**
     * Ensure the grade is accessed from a single device.
     */
    protected function ensureSingleDevice(Grade $grade, ?string $deviceToken, array $deviceInfo = []): ?array
    {
        if (!$deviceToken) {
            return null;
        }

        $now = Carbon::now();

        if (!$grade->device_token) {
            $grade->device_token = $deviceToken;
            $grade->device_info = $deviceInfo;
            $grade->last_seen_at = $now;
            $grade->save();

            return null;
        }

        if (hash_equals($grade->device_token, (string) $deviceToken)) {
            $grade->last_seen_at = $now;
            if (!empty($deviceInfo)) {
                $grade->device_info = array_replace((array) $grade->device_info, $deviceInfo);
            }
            $grade->save();

            return null;
        }

        $examGroup = $this->resolveExamGroup($grade);

        if ($examGroup) {
            CheatEvent::create([
                'student_id' => $grade->student_id,
                'exam_id' => $grade->exam_id,
                'exam_session_id' => $grade->exam_session_id,
                'grade_id' => $grade->id,
                'type' => 'MULTI_DEVICE',
                'meta' => [
                    'expected_token' => $grade->device_token,
                    'incoming_token' => $deviceToken,
                    'existing_device_info' => $grade->device_info,
                    'incoming_device_info' => $deviceInfo,
                ],
            ]);

            $grade->cheat_count = ($grade->cheat_count ?? 0) + 1;
            $grade->cheat_status = 'LOCKED';
            $grade->cheat_reason = 'Perangkat berbeda terdeteksi';
            $grade->save();

            if ($examGroup->student) {
                $examGroup->student->is_locked = true;
                $examGroup->student->locked_at = $now;
                $examGroup->student->save();
            }

            $this->finalizeGrade($grade->refresh(), $examGroup, true);

            return [
                'message' => 'Perangkat berbeda terdeteksi. Ujian dikunci.',
                'redirect_to' => route('student.exams.resultExam', $examGroup->id),
            ];
        }

        return [
            'message' => 'Perangkat berbeda terdeteksi.',
            'redirect_to' => route('student.dashboard'),
        ];
    }
}
