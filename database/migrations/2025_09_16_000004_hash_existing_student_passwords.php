<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('students')
            ->select('id', 'password')
            ->orderBy('id')
            ->chunkById(200, function ($students): void {
                foreach ($students as $student) {
                    $password = $student->password;

                    if (empty($password)) {
                        continue;
                    }

                    if (Str::startsWith($password, ['$2y$', '$2a$', '$argon2i$', '$argon2id$', '$P$'])) {
                        continue;
                    }

                    DB::table('students')
                        ->where('id', $student->id)
                        ->update([
                            'password' => Hash::make($password),
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Password hashing is irreversible; no action performed on rollback.
    }
};

