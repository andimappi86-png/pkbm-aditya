<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classrooms', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_rombel', 100);
            $table->string('kode_rombel', 30)->unique();
            $table->foreignId('program_id')
                ->constrained()
                ->restrictOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('tahun_ajaran_id')
                ->constrained('academic_years')
                ->restrictOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('wali_kelas_id')
                ->nullable()
                ->constrained('users')
                ->nullOnUpdate()
                ->nullOnDelete();
            $table->boolean('status_aktif')->default(true)->index();
            $table->timestamps();

            $table->index(['program_id', 'tahun_ajaran_id']);
            $table->index(['tahun_ajaran_id', 'status_aktif']);
            $table->index('wali_kelas_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};
