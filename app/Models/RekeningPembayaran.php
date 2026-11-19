<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekeningPembayaran extends Model
{
    use HasFactory;

    protected $table = 'rekening_pembayaran';

    protected $fillable = [
        'nama_bank',
        'nomor_rekening',
        'atas_nama',
        'foto_qris',
        'is_active',
    ];

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'rekening_id');
    }
}
