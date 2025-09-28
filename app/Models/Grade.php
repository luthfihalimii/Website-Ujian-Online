<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'exam_id',
        'exam_session_id',
        'student_id',
        'duration',
        'start_time',
        'end_time',
        'total_correct',
        'grade',
        'cheat_count',
        'last_cheat_at',
        'cheat_status',
        'cheat_reason',
    ];

    protected $casts = [
        'last_cheat_at' => 'datetime',
    ];

    /**
     * exam
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * exam_session
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function exam_session()
    {
        return $this->belongsTo(ExamSession::class);
    }

    /**
     * student
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
