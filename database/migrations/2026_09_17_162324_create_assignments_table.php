<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penugasan_mengajar_id')->constrained()->cascadeOnDelete();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->text('instruksi')->nullable();
            $table->timestamp('deadline')->nullable();
            $table->string('file_materi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('type')->default('tugas'); // 'tugas' atau 'quiz'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
