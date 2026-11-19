<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pemesanan extends Model
{
    use HasFactory;

    protected $table = 'pemesanan';

    protected $fillable = [
        'kode_booking',
        'user_id',
        'lapangan_id',
        'tanggal_main',
        'jam_mulai',
        'durasi_jam',
        'jam_selesai',
        'total_harga',
        'status',
        'batas_pembayaran',
        'catatan',
    ];

    protected $casts = [
        'tanggal_main' => 'date',
        'batas_pembayaran' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lapangan()
    {
        return $this->belongsTo(Lapangan::class);
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class);
    }
}
