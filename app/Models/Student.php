<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Student extends Authenticatable
{
    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'classroom_id',
        'nisn',
        'name',
        'password',
        'gender',
        'is_locked',
        'locked_at',
    ];

    /**
     * Hide sensitive attributes from array/JSON casts.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Automatically hash passwords when they are assigned.
     */
    public function setPasswordAttribute($value): void
    {
        if (!filled($value)) {
            return;
        }

        if ($this->isHashedPassword($value)) {
            $this->attributes['password'] = $value;
            return;
        }

        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Determine if the supplied password string is already hashed.
     */
    protected function isHashedPassword(string $value): bool
    {
        return Str::startsWith($value, [
            '$2y$', // bcrypt
            '$2a$',
            '$argon2i$',
            '$argon2id$',
            '$P$',
        ]);
    }

    /**
     * classroom
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * cheatEvents
     */
    public function cheatEvents()
    {
        return $this->hasMany(CheatEvent::class);
    }

    /**
     * Active login sessions for the student.
     */
    public function loginSessions()
    {
        return $this->hasMany(StudentLoginSession::class);
    }
}
