<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encrypted_dummies', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'student' or 'tutor'
            $table->string('encrypted_nik');
            $table->string('encrypted_nama');
            $table->string('encrypted_email');
            $table->string('encrypted_alamat');
            $table->string('encrypted_telepon');
            $table->timestamps();

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encrypted_dummies');
    }
};
