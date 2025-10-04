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
        'device_token',
        'device_info',
        'last_seen_at',
        'last_answered_at',
    ];

    protected $casts = [
        'last_cheat_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'last_answered_at' => 'datetime',
        'device_info' => 'array',
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
