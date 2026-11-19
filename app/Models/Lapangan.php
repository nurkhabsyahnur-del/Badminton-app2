<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lapangan extends Model
{
    use HasFactory;

    protected $table = 'lapangan';

    protected $fillable = [
        'nomor_lapangan',
        'nama_lapangan',
        'jenis_karpet',
        'harga_per_jam',
        'foto',
        'deskripsi',
        'status',
    ];

    public function pemesanan()
    {
        return $this->hasMany(Pemesanan::class);
    }
}
