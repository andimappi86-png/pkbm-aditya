<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->text('jawaban')->nullable();
            $table->string('file_jawaban')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->string('status')->default('pending'); // pending, submitted, graded
            $table->text('feedback')->nullable();
            $table->decimal('nilai', 5, 2)->nullable();
            $table->timestamps();

            // Satu siswa satu pengumpulan per tugas
            $table->unique(['assignment_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
