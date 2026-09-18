<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_classroom', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('classroom_id')->constrained('classrooms')->onDelete('cascade');
            $table->unique(['subject_id', 'classroom_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_classroom');
    }
};
