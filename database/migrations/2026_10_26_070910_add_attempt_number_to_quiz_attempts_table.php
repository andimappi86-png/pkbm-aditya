<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add attempt_number column only if not exists
        if (! Schema::hasColumn('quiz_attempts', 'attempt_number')) {
            Schema::table('quiz_attempts', function (Blueprint $table) {
                $table->unsignedTinyInteger('attempt_number')->default(1)->after('student_id');
            });
        }

        // Backfill attempt_number for existing records
        $attempts = DB::table('quiz_attempts')
            ->select('id', 'quiz_id', 'student_id')
            ->orderBy('mulai_waktu')
            ->get();

        $attemptCounts = [];
        foreach ($attempts as $attempt) {
            $key = $attempt->quiz_id . '_' . $attempt->student_id;
            $attemptCounts[$key] = ($attemptCounts[$key] ?? 0) + 1;

            DB::table('quiz_attempts')
                ->where('id', $attempt->id)
                ->update(['attempt_number' => $attemptCounts[$key]]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('quiz_attempts', 'attempt_number')) {
            Schema::table('quiz_attempts', function (Blueprint $table) {
                $table->dropColumn('attempt_number');
            });
        }
    }
};