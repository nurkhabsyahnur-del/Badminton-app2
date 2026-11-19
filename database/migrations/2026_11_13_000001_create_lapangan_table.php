<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lapangan', function (Blueprint $table) {
            $table->id();
            $table->integer('nomor_lapangan')->unique();
            $table->string('nama_lapangan');
            $table->string('jenis_karpet')->default('Vinyl Standar PBSI');
            $table->decimal('harga_per_jam', 10, 2)->default(35000);
            $table->string('foto')->default('lapangan_default.jpg');
            $table->text('deskripsi')->nullable();
            $table->enum('status', ['tersedia', 'perbaikan'])->default('tersedia');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lapangan');
    }
};
