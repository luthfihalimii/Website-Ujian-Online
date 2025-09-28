<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->unsignedInteger('cheat_count')->default(0)->after('grade');
            $table->timestamp('last_cheat_at')->nullable()->after('cheat_count');
            $table->string('cheat_status')->default('OK')->after('last_cheat_at'); // OK|WARNED|LOCKED
            $table->text('cheat_reason')->nullable()->after('cheat_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn(['cheat_count', 'last_cheat_at', 'cheat_status', 'cheat_reason']);
        });
    }
};
