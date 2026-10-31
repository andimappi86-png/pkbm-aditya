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
        Schema::table('quiz_answers', function (Blueprint $table) {
            $table->renameColumn('jawaban_teks', 'answer_text');
            $table->decimal('nilai', 6, 2)->nullable()->after('benar');
            $table->text('catatan')->nullable()->after('nilai');
            $table->foreignId('dinilai_oleh')->after('catatan')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dinilai_pada')->nullable()->after('dinilai_oleh');
            $table->string('media_path')->nullable()->after('dinilai_pada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_answers', function (Blueprint $table) {
            $table->dropForeign(['dinilai_oleh']);
            $table->dropColumn([
                'answer_text',
                'nilai',
                'catatan',
                'dinilai_oleh',
                'dinilai_pada',
                'media_path',
            ]);
            $table->renameColumn('answer_text', 'jawaban_teks');
        });
    }
};
