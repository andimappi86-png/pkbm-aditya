<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tutors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnUpdate()->nullOnDelete();

            // Identitas
            $table->string('nomor_induk', 50)->unique();
            $table->string('nik', 50)->nullable()->unique();
            $table->string('nama_lengkap', 150);
            $table->string('nama_panggilan', 50)->nullable();
            $table->string('jenis_kelamin', 20);
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('agama', 50)->nullable();

            // Kontak & Alamat
            $table->text('alamat')->nullable();
            $table->string('no_hp', 30)->nullable();
            $table->string('email', 100)->nullable();

            // Profesional
            $table->string('pendidikan_terakhir', 100)->nullable();
            $table->string('bidang_keahlian', 150)->nullable();
            $table->string('status_tutor', 30)->default('aktif')->index();
            $table->date('tanggal_mulai_mengajar')->nullable();

            // Tambahan
            $table->string('foto')->nullable();
            $table->text('catatan')->nullable();

            $table->timestamps();

            $table->index(['nama_lengkap']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutors');
    }
};
