<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Kolom legacy keluarga menjadi opsional; detail normalisasi disimpan di parent_details.
            $table->text('nama_ayah')->nullable()->change();
            $table->text('nama_ibu')->nullable()->change();

            // Bagian A
            $table->string('nomor_akta_lahir', 50)->nullable();
            $table->text('nomor_kk')->nullable();
            $table->tinyInteger('urutan_anak')->nullable();

            // Bagian B
            $table->text('titik_koordinat')->nullable();
            $table->string('jenis_tempat_tinggal', 50)->nullable();
            $table->string('alat_transportasi', 50)->nullable();
            $table->string('jarak_rumah', 50)->nullable();

            // Bagian C
            $table->string('kebutuhan_khusus', 100)->nullable();
            $table->decimal('berat_badan', 5, 2)->nullable();
            $table->decimal('tinggi_badan', 5, 2)->nullable();
            $table->decimal('lingkar_kepala', 5, 2)->nullable();

            // Bagian E
            $table->boolean('kps_status')->nullable();
            $table->string('nomor_kps', 50)->nullable();
            $table->boolean('pkh_status')->nullable();
            $table->boolean('kip_status')->nullable();
            $table->string('nama_kip', 150)->nullable();
            $table->string('kks_nomor', 50)->nullable();
            $table->boolean('pip_kelayakan')->nullable();
            $table->string('pip_alasan', 255)->nullable();
            $table->string('bank_nama', 100)->nullable();
            $table->text('rekening_nomor')->nullable();
            $table->string('rekening_nama', 150)->nullable();

            // Bagian F
            $table->string('nomor_peserta_un', 100)->nullable();
            $table->string('ijazah_nomor', 100)->nullable();
            $table->string('skhun_nomor', 100)->nullable();
        });

        Schema::create('parent_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('relationship_type', 20); // ayah, ibu, wali
            $table->text('nik')->nullable();
            $table->string('name', 150);
            $table->integer('birth_year')->nullable();
            $table->string('education', 50)->nullable();
            $table->string('occupation', 100)->nullable();
            $table->decimal('monthly_income', 15, 2)->nullable();
            $table->text('phone')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'relationship_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_details');
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'nomor_akta_lahir', 'nomor_kk', 'urutan_anak',
                'titik_koordinat', 'jenis_tempat_tinggal', 'alat_transportasi', 'jarak_rumah',
                'kebutuhan_khusus', 'berat_badan', 'tinggi_badan', 'lingkar_kepala',
                'kps_status', 'nomor_kps', 'pkh_status', 'kip_status', 'nama_kip', 'kks_nomor', 'pip_kelayakan', 'pip_alasan', 'bank_nama', 'rekening_nomor', 'rekening_nama',
                'nomor_peserta_un', 'ijazah_nomor', 'skhun_nomor',
            ]);
        });
    }
};
