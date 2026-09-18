<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('judul');
            $table->text('isi');
            $table->string('kategori');
            $table->string('target_role')->nullable();
            $table->foreignId('target_program')->nullable()->constrained('programs')->nullOnDelete();
            $table->foreignId('target_rombel')->nullable()->constrained('classrooms')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();

            $table->index(['target_role', 'target_program', 'target_rombel']);
            $table->index(['status_aktif', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
