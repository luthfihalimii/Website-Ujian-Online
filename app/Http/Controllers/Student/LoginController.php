<?php

namespace App\Http\Controllers\Student;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Services\StudentDeviceManager;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, StudentDeviceManager $deviceManager)
    {
        //validate the form data
        $request->validate([
            'nisn'      => 'required',
            'password'  => 'required',
        ]);

        //cek nisn dan password
        $student = Student::where('nisn', $request->nisn)->first();

        if(!$student || !Hash::check($request->password, $student->password)) {
            return redirect()->back()->with('error', 'NISN atau Password salah');
        }
        if ($student->is_locked ?? false) {
            return redirect()->back()->with('error', 'Akun Anda terkunci. Hubungi admin untuk membuka kunci.');
        }

        //login the user and regenerate session id
        auth()->guard('student')->login($student);
        $request->session()->regenerate();

        // register active session & invalidate others
        $deviceManager->registerLogin($student, $request);

        //redirect to dashboard
        return redirect()->route('student.dashboard');
    }
}
