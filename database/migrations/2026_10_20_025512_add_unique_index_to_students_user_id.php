<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEX_NAME = 'students_user_id_unique';

    public function up(): void
    {
        if (Schema::hasIndex('students', self::INDEX_NAME, 'unique')) {
            return;
        }

        $hasDuplicates = DB::table('students')
            ->whereNotNull('user_id')
            ->select(['user_id'])
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->limit(1)
            ->exists();

        if ($hasDuplicates) {
            throw new RuntimeException(
                'Tidak dapat menambahkan unique index '.self::INDEX_NAME.': ditemukan duplikasi '
                .'students.user_id (non-NULL). Rekonsiliasi manual diperlukan sebelum migration dijalankan.'
            );
        }

        Schema::table('students', function (Blueprint $table) {
            $table->unique('user_id', self::INDEX_NAME);
        });
    }

    public function down(): void
    {
        if (! Schema::hasIndex('students', self::INDEX_NAME, 'unique')) {
            return;
        }

        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(self::INDEX_NAME);
        });
    }
};
