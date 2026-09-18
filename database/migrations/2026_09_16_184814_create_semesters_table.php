<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semesters', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tahun_ajaran_id')
                ->constrained('academic_years')
                ->restrictOnUpdate()
                ->restrictOnDelete();
            $table->string('nama_semester', 20);
            $table->string('kode', 20);
            $table->boolean('status_aktif')->default(true)->index();
            $table->timestamps();

            $table->unique(['tahun_ajaran_id', 'kode']);
            $table->index(['tahun_ajaran_id', 'status_aktif']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};
