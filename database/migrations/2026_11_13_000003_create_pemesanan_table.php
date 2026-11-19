<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemesanan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_booking')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('lapangan_id')->constrained('lapangan')->onDelete('cascade');
            $table->date('tanggal_main');
            $table->time('jam_mulai');
            $table->integer('durasi_jam')->default(1);
            $table->time('jam_selesai');
            $table->decimal('total_harga', 10, 2);
            $table->enum('status', [
                'menunggu_pembayaran', 
                'menunggu_konfirmasi', 
                'terkonfirmasi', 
                'dibatalkan', 
                'selesai'
            ])->default('menunggu_pembayaran');
            $table->dateTime('batas_pembayaran');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['lapangan_id', 'tanggal_main', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemesanan');
    }
};
