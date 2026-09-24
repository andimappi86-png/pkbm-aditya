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
        Schema::table('students', function (Blueprint $table) {
            $table->text('nik')->change();
            $table->text('alamat')->change();
            $table->text('no_hp')->change();
            $table->text('email')->nullable()->change();
            $table->text('nama_ayah')->change();
            $table->text('nama_ibu')->change();
            $table->text('nama_wali')->nullable()->change();
            $table->text('no_hp_wali')->nullable()->change();
            $table->text('alamat_wali')->nullable()->change();
        });

        Schema::table('tutors', function (Blueprint $table) {
            $table->text('nik')->change();
            $table->text('alamat')->change();
            $table->text('no_hp')->change();
            $table->text('email')->nullable()->change();
            $table->text('bidang_keahlian')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('nik')->change();
            $table->string('alamat')->change();
            $table->date('tanggal_lahir')->change();
            $table->string('no_hp')->change();
            $table->string('email')->nullable()->change();
            $table->string('nama_ayah')->change();
            $table->string('nama_ibu')->change();
            $table->string('nama_wali')->nullable()->change();
            $table->string('no_hp_wali')->nullable()->change();
            $table->string('alamat_wali')->nullable()->change();
        });

        Schema::table('tutors', function (Blueprint $table) {
            $table->string('nik')->change();
            $table->string('alamat')->change();
            $table->date('tanggal_lahir')->change();
            $table->string('no_hp')->change();
            $table->string('email')->nullable()->change();
            $table->string('bidang_keahlian')->nullable()->change();
        });
    }
};
