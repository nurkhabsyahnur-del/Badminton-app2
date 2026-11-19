<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemesanan_id')->unique()->constrained('pemesanan')->onDelete('cascade');
            $table->foreignId('rekening_id')->nullable()->constrained('rekening_pembayaran')->onDelete('set null');
            $table->enum('metode_pembayaran', ['transfer_bank', 'qris', 'tunai'])->default('transfer_bank');
            $table->string('bank_pengirim')->nullable();
            $table->string('nama_pengirim')->nullable();
            $table->decimal('jumlah_dibayar', 10, 2);
            $table->longText('bukti_transfer');
            $table->enum('status_verifikasi', ['menunggu', 'valid', 'tidak_valid'])->default('menunggu');
            $table->text('catatan_admin')->nullable();
            $table->dateTime('tanggal_bayar')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
