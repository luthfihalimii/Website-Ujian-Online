<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TimeSyncController extends Controller
{
    /**
     * Return the current server timestamp in milliseconds.
     */
    public function __invoke(Request $request)
    {
        $now = Carbon::now();

        return response()->json([
            'timestamp' => $now->valueOf(),
            'iso' => $now->toIso8601String(),
            'timezone' => $now->getTimezone()->getName(),
        ]);
    }
}
