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
        Schema::table('quizzes', function (Blueprint $table) {
            $table->unsignedTinyInteger('max_attempts')->default(1)->after('status_aktif');
            $table->timestamp('buka_waktu')->nullable()->after('max_attempts');
            $table->timestamp('tutup_waktu')->nullable()->after('buka_waktu');
            $table->boolean('acak_soal')->default(false)->after('tutup_waktu');
            $table->enum('tampilkan_jawaban', ['never', 'after_submit', 'after_deadline'])->default('never')->after('acak_soal');
            $table->enum('tampilkan_nilai', ['never', 'after_submit', 'after_grading'])->default('never')->after('tampilkan_jawaban');
            $table->index(['buka_waktu', 'tutup_waktu', 'status_aktif'], 'quizzes_schedule_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropIndex('quizzes_schedule_status_index');
            $table->dropColumn([
                'max_attempts',
                'buka_waktu',
                'tutup_waktu',
                'acak_soal',
                'tampilkan_jawaban',
                'tampilkan_nilai',
            ]);
        });
    }
};