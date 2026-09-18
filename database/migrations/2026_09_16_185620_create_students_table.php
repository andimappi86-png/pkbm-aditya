<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnUpdate()->nullOnDelete();

            // Identitas
            $table->string('nis', 50)->unique();
            $table->string('nisn', 50)->nullable()->unique();
            $table->string('nik', 50);
            $table->string('nama_lengkap', 150);
            $table->string('nama_panggilan', 50)->nullable();
            $table->string('jenis_kelamin', 20);
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('agama', 50)->nullable();
            $table->string('status_perkawinan', 50)->nullable();

            // Alamat & Kontak
            $table->text('alamat');
            $table->string('rt', 10)->nullable();
            $table->string('rw', 10)->nullable();
            $table->string('kelurahan', 100)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('kabupaten', 100)->nullable();
            $table->string('provinsi', 100)->nullable();
            $table->string('no_hp', 30)->nullable();
            $table->string('email', 100)->nullable();

            // Pendidikan
            $table->foreignId('program_id')->constrained('programs')->restrictOnUpdate()->restrictOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('academic_years')->restrictOnUpdate()->restrictOnDelete();
            $table->foreignId('rombel_id')->nullable()->constrained('classrooms')->nullOnUpdate()->nullOnDelete();
            $table->string('status', 30)->default('aktif')->index();

            // Keluarga
            $table->string('nama_ayah', 150)->nullable();
            $table->string('nama_ibu', 150)->nullable();
            $table->string('nama_wali', 150)->nullable();
            $table->string('no_hp_wali', 30)->nullable();
            $table->text('alamat_wali')->nullable();

            // Tambahan
            $table->string('foto')->nullable();
            $table->text('catatan')->nullable();

            $table->timestamps();

            $table->index(['program_id', 'tahun_ajaran_id', 'status']);
            $table->index(['nama_lengkap']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
