<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Ubah diskusi dari berbasis matkul menjadi berbasis topik bebas: buang course_id,
// ganti dengan kolom string 'topic'.
return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::table('discussions', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropColumn('course_id');
            $table->string('topic')->after('user_id'); // topik diskusi bebas (mis. "Laravel", "Tugas Akhir")
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::table('discussions', function (Blueprint $table) {
            $table->dropColumn('topic');
            $table->foreignId('course_id')->nullable()->constrained()->onDelete('cascade');
        });
    }
};
