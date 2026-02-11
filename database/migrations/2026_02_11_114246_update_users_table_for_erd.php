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
        Schema::table('users', function (Blueprint $table) {
            $table->string('nim')->unique()->nullable()->after('role');
            $table->string('nip')->unique()->nullable()->after('nim');
            $table->string('sso_id')->unique()->nullable()->after('nip');
            $table->boolean('is_active')->default(true)->after('sso_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nim', 'nip', 'sso_id', 'is_active']);
        });
    }
};
