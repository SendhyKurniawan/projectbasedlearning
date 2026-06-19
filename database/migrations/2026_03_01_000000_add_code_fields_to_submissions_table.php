<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tambahkan kolom khusus exercise ke submissions: jawaban kode, hasil validasi (hint), & flag auto-grade.
return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->text('code_answer')->nullable();         // jawaban kode mahasiswa
            $table->json('validation_result')->nullable();   // hasil validasi keyword (hint untuk dosen)
            $table->boolean('auto_graded')->default(false);  // apakah dinilai otomatis (selalu false untuk exercise)
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['code_answer', 'validation_result', 'auto_graded']);
        });
    }
};
