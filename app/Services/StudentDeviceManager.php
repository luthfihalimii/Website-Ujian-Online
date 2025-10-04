<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentLoginSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentDeviceManager
{
    /**
     * Register the current login session for the student and invalidate others.
     */
    public function registerLogin(Student $student, Request $request): void
    {
        $sessionId = (string) $request->session()->getId();
        $fingerprint = $this->fingerprint($request);
        $userAgent = Str::limit((string) $request->userAgent(), 255, '');
        $ipAddress = $request->ip();
        $now = Carbon::now();

        DB::transaction(function () use ($student, $sessionId, $fingerprint, $userAgent, $ipAddress, $now, $request) {
            $this->invalidateOtherSessions($student, $sessionId);

            StudentLoginSession::updateOrCreate(
                ['session_id' => $sessionId],
                [
                    'student_id' => $student->id,
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                    'fingerprint' => $fingerprint,
                    'device_details' => $this->buildDeviceDetails($request),
                    'last_used_at' => $now,
                ]
            );
        });
    }

    /**
     * Validate that the incoming request matches the stored session fingerprint.
     */
    public function validateSession(Student $student, Request $request): bool
    {
        $sessionId = (string) $request->session()->getId();

        /** @var StudentLoginSession|null $session */
        $session = StudentLoginSession::where('student_id', $student->id)
            ->where('session_id', $sessionId)
            ->first();

        if (!$session) {
            return false;
        }

        $expected = (string) ($session->fingerprint ?? '');
        $actual = $this->fingerprint($request);

        if (!hash_equals($expected, $actual)) {
            $this->invalidateSessionId($session->session_id);
            $session->delete();

            return false;
        }

        $session->fill([
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 255, ''),
            'last_used_at' => Carbon::now(),
            'device_details' => array_replace(
                (array) $session->device_details,
                $this->buildDeviceDetails($request)
            ),
        ]);

        if ($session->isDirty()) {
            $session->save();
        }

        return true;
    }

    /**
     * Destroy all other sessions for the student.
     */
    protected function invalidateOtherSessions(Student $student, string $currentSessionId): void
    {
        $sessions = StudentLoginSession::where('student_id', $student->id)
            ->where('session_id', '!=', $currentSessionId)
            ->get();

        foreach ($sessions as $session) {
            $this->invalidateSessionId($session->session_id);
            $session->delete();
        }
    }

    /**
     * Destroy a specific session using the configured session handler.
     */
    protected function invalidateSessionId(?string $sessionId): void
    {
        if (!$sessionId) {
            return;
        }

        try {
            app('session')->getHandler()->destroy($sessionId);
        } catch (\Throwable $th) {
            // swallow handler failures and fallback to database cleanup
        }

        if (config('session.driver') === 'database') {
            $table = config('session.table', 'sessions');
            DB::table($table)->where('id', $sessionId)->delete();
        }
    }

    /**
     * Create a consistent fingerprint from IP and user agent data.
     */
    protected function fingerprint(Request $request): string
    {
        $ip = (string) $request->ip();
        $agent = Str::limit((string) $request->userAgent(), 255, '');

        return hash('sha256', $ip . '|' . $agent);
    }

    /**
     * Extract lightweight device metadata from the request.
     */
    protected function buildDeviceDetails(Request $request): array
    {
        return array_filter([
            'accept_language' => $request->header('Accept-Language'),
            'sec_ch_platform' => $request->header('Sec-CH-UA-Platform'),
            'sec_ch_model' => $request->header('Sec-CH-UA-Model'),
        ], fn ($value) => filled($value));
    }
}
