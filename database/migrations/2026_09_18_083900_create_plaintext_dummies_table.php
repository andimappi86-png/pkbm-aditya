<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plaintext_dummies', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'student' or 'tutor'
            $table->string('nik');
            $table->string('nama');
            $table->string('email');
            $table->string('alamat');
            $table->string('telepon');
            $table->timestamps();

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plaintext_dummies');
    }
};
