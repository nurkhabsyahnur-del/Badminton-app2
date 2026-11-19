<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Upload bukti transfer disimpan sebagai data URI saat di-deploy ke Vercel,
     * sehingga kolom harus bisa menampung base64 gambar.
     */
    public function up(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->longText('bukti_transfer')->change();
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->string('bukti_transfer', 2048)->change();
        });
    }
};
