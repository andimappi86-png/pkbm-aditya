<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table): void {
            $table->id();
            $table->string('nama', 20)->unique();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->boolean('status_aktif')->default(true)->index();
            $table->timestamps();

            $table->index(['tanggal_mulai', 'tanggal_selesai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_years');
    }
};
