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
        Schema::create('ppdbs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nomor_pendaftaran')->unique();
            $table->foreignId('program_id')->constrained()->restrictOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('academic_years')->restrictOnDelete();

            // Data identitas calon peserta didik
            $table->string('nik')->unique();
            $table->string('nisn')->nullable()->unique();
            $table->string('nama_lengkap');
            $table->string('nama_panggilan')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->string('agama');
            $table->string('alamat');
            $table->string('rt')->nullable();
            $table->string('rw')->nullable();
            $table->string('kelurahan')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kabupaten');
            $table->string('provinsi');
            $table->string('no_hp');
            $table->string('email')->unique();

            // Data orang tua/wali
            $table->string('nama_ayah');
            $table->string('pekerjaan_ayah')->nullable();
            $table->string('no_hp_ayah')->nullable();
            $table->string('nama_ibu');
            $table->string('pekerjaan_ibu')->nullable();
            $table->string('no_hp_ibu')->nullable();
            $table->string('nama_wali')->nullable();
            $table->string('pekerjaan_wali')->nullable();
            $table->string('no_hp_wali')->nullable();
            $table->string('alamat_wali')->nullable();

            // Dokumen persyaratan
            $table->string('dok_ktp_ayah')->nullable();
            $table->string('dok_ktp_ibu')->nullable();
            $table->string('dok_kk')->nullable();
            $table->string('dok_akta_kelahiran')->nullable();
            $table->string('dok_ijasah')->nullable();
            $table->string('dok_skhun')->nullable();
            $table->string('dok_foto')->nullable();
            $table->string('dok_ktp_wali')->nullable();

            // Status pendaftaran
            $table->enum('status', ['pending', 'diverifikasi', 'diterima', 'ditolak'])->default('pending');
            $table->text('catatan_verifikasi')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('tanggal_verifikasi')->nullable();

            $table->timestamps();

            $table->index(['status', 'program_id', 'tahun_ajaran_id']);
            $table->index('nomor_pendaftaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppdbs');
    }
};
