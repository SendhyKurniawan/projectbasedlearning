<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tambahkan kolom 'order' ke assignments agar tugas bisa diurutkan (drag-and-drop) per matkul.
return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->integer('order')->default(0)->after('title'); // urutan tampil tugas
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
};
