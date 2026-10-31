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
        Schema::create('quiz_grade_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tutor_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('nilai_lama', 6, 2)->nullable();
            $table->decimal('nilai_baru', 6, 2)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['quiz_attempt_id', 'question_id'], 'quiz_grade_logs_attempt_question_index');
            $table->index('tutor_id', 'quiz_grade_logs_tutor_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_grade_logs');
    }
};