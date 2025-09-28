<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

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
}
