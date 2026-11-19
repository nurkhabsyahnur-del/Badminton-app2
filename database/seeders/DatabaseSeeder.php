<?php

namespace Database\Seeders;

use App\Models\Lapangan;
use App\Models\RekeningPembayaran;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users Default (idempotent, aman dijalankan ulang saat deploy)
        User::firstOrCreate(
            ['email' => 'admin@gormetro.com'],
            [
                'name' => 'Pengurus GOR Metro',
                'username' => 'admin',
                'no_hp' => '0887437641573',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'user@gmail.com'],
            [
                'name' => 'Pelanggan Contoh',
                'username' => 'pelanggan',
                'no_hp' => '089876543210',
                'password' => Hash::make('password123'),
                'role' => 'pelanggan',
            ]
        );

        // 2. Lapangan Default (5 Lapangan)
        $lapangans = [
            [
                'nomor_lapangan' => 1,
                'nama_lapangan' => 'Lapangan 1 (Dekat Pintu Masuk)',
                'jenis_karpet' => 'Vinyl Standar PBSI',
                'harga_per_jam' => 35000,
                'deskripsi' => 'Pencahayaan terang LED, lantai vinyl empuk standar PBSI.',
                'status' => 'tersedia',
            ],
            [
                'nomor_lapangan' => 2,
                'nama_lapangan' => 'Lapangan 2',
                'jenis_karpet' => 'Vinyl Standar PBSI',
                'harga_per_jam' => 35000,
                'deskripsi' => 'Lantai vinyl kualitas tinggi, sirkulasi udara baik.',
                'status' => 'tersedia',
            ],
            [
                'nomor_lapangan' => 3,
                'nama_lapangan' => 'Lapangan 3 (Tengah)',
                'jenis_karpet' => 'Vinyl Standar PBSI',
                'harga_per_jam' => 35000,
                'deskripsi' => 'Posisi strategis di tengah, cocok untuk turnamen lokal.',
                'status' => 'tersedia',
            ],
            [
                'nomor_lapangan' => 4,
                'nama_lapangan' => 'Lapangan 4',
                'jenis_karpet' => 'Vinyl Standar PBSI',
                'harga_per_jam' => 35000,
                'deskripsi' => 'Lapangan bersih dan terawat dengan garis lapangan standar.',
                'status' => 'tersedia',
            ],
            [
                'nomor_lapangan' => 5,
                'nama_lapangan' => 'Lapangan 5 (Ujung)',
                'jenis_karpet' => 'Vinyl Standar PBSI',
                'harga_per_jam' => 35000,
                'deskripsi' => 'Suasana lebih privat dengan pencahayaan optimal.',
                'status' => 'tersedia',
            ],
        ];

        foreach ($lapangans as $lap) {
            Lapangan::firstOrCreate(['nomor_lapangan' => $lap['nomor_lapangan']], $lap);
        }

        // 3. Rekening Pembayaran Default
        $rekenings = [
            [
                'nama_bank' => 'Bank BCA',
                'nomor_rekening' => '1234567890',
                'atas_nama' => 'GOR METRO BADMINTON',
                'is_active' => true,
            ],
            [
                'nama_bank' => 'Bank Mandiri',
                'nomor_rekening' => '9876543210123',
                'atas_nama' => 'GOR METRO BADMINTON',
                'is_active' => true,
            ],
            [
                'nama_bank' => 'Bank BRI',
                'nomor_rekening' => '543201098765432',
                'atas_nama' => 'GOR METRO BADMINTON',
                'is_active' => true,
            ],
        ];

        foreach ($rekenings as $rekening) {
            RekeningPembayaran::firstOrCreate(
                ['nomor_rekening' => $rekening['nomor_rekening']],
                $rekening
            );
        }
    }
}
