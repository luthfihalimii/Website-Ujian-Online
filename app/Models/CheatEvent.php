<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheatEvent extends Model
{
    protected $fillable = [
        'student_id',
        'exam_id',
        'exam_session_id',
        'grade_id',
        'type', // e.g., TAB_BLUR, VISIBILITY_HIDDEN, CONTEXT_MENU, KEYBOARD_COPY, FULLSCREEN_EXIT, DEVTOOLS
        'meta', // optional details
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function student() { return $this->belongsTo(Student::class); }
    public function exam() { return $this->belongsTo(Exam::class); }
    public function exam_session() { return $this->belongsTo(ExamSession::class); }
    public function grade() { return $this->belongsTo(Grade::class); }
}
