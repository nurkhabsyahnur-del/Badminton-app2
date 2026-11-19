<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';

    protected $fillable = [
        'pemesanan_id',
        'rekening_id',
        'metode_pembayaran',
        'bank_pengirim',
        'nama_pengirim',
        'jumlah_dibayar',
        'bukti_transfer',
        'status_verifikasi',
        'catatan_admin',
        'tanggal_bayar',
    ];

    protected $casts = [
        'tanggal_bayar' => 'datetime',
    ];

    /**
     * Mendukung dua format bukti:
     * - data URI (disimpan saat di Vercel)
     * - nama file di storage/app/public/bukti_transfer (local)
     */
    public function getBuktiTransferUrlAttribute(): string
    {
        $bukti = (string) $this->bukti_transfer;

        if (str_starts_with($bukti, 'data:image/')) {
            return $bukti;
        }

        return asset('storage/bukti_transfer/'.$bukti);
    }

    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class);
    }

    public function rekening()
    {
        return $this->belongsTo(RekeningPembayaran::class, 'rekening_id');
    }
}
