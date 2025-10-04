<?php

namespace App\Http\Middleware;

use App\Services\StudentDeviceManager;
use Closure;
use Illuminate\Http\Request;

class AuthStudent
{
    public function __construct(private readonly StudentDeviceManager $deviceManager)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        //check if user is logged in
        $student = auth()->guard('student')->user();

        //if not, redirect to login page
        if (!$student) {
            return redirect('/');
        }

        // if student account is locked, prevent access
        if ($student->is_locked ?? false) {
            // if ajax/json, return 423 Locked (plain response to satisfy return type)
            if ($request->expectsJson()) {
                return response('Akun Anda terkunci.', 423);
            }
            // logout and redirect to login page to avoid redirect loop
            auth()->guard('student')->logout();
            return redirect('/')->with('error', 'Akun Anda terkunci. Hubungi admin.');
        }

        if (!$this->deviceManager->validateSession($student, $request)) {
            auth()->guard('student')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = 'Sesi Anda berakhir karena terdeteksi login dari perangkat lain.';

            if ($request->expectsJson()) {
                return response($message, 440);
            }

            return redirect('/')->with('error', $message);
        }

        //if user is logged in, continue to next middleware
        return $next($request);
    }
}
