<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Buat tabel announcements (pengumuman). target_audience menentukan sasaran pembaca.
return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // pembuat pengumuman
            $table->string('title');
            $table->text('content');
            $table->enum('target_audience', ['all', 'dosen', 'mahasiswa', 'specific'])->default('all'); // sasaran pembaca
            $table->timestamps();
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
