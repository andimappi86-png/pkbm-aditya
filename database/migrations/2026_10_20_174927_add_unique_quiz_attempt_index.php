<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEX_NAME = 'quiz_attempts_quiz_id_student_id_unique';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasIndex('quiz_attempts', self::INDEX_NAME, 'unique')) {
            return;
        }

        $hasDuplicates = DB::table('quiz_attempts')
            ->select(['quiz_id', 'student_id'])
            ->groupBy(['quiz_id', 'student_id'])
            ->havingRaw('COUNT(*) > 1')
            ->limit(1)
            ->exists();

        if ($hasDuplicates) {
            throw new RuntimeException(
                'Tidak dapat menambahkan unique index '.self::INDEX_NAME.': ditemukan attempt duplikat '
                .'untuk kombinasi quiz_id + student_id. Rekonsiliasi manual diperlukan sebelum migration dijalankan.'
            );
        }

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->unique(['quiz_id', 'student_id'], self::INDEX_NAME);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasIndex('quiz_attempts', self::INDEX_NAME, 'unique')) {
            return;
        }

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropUnique(self::INDEX_NAME);
        });
    }
};
