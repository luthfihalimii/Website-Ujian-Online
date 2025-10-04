<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentLoginSession extends Model
{
    protected $fillable = [
        'student_id',
        'session_id',
        'ip_address',
        'user_agent',
        'fingerprint',
        'device_details',
        'last_used_at',
    ];

    protected $casts = [
        'device_details' => 'array',
        'last_used_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
