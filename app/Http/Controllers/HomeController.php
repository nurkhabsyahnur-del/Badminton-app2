<?php

namespace App\Http\Controllers;

use App\Models\Lapangan;

class HomeController extends Controller
{
    public function index()
    {
        try {
            $lapangans = Lapangan::orderBy('nomor_lapangan', 'asc')->get();
        } catch (\Throwable $e) {
            $lapangans = collect();
        }

        if ($lapangans->isEmpty()) {
            $lapangans = collect([
                (object)[
                    'id' => 1,
                    'nomor_lapangan' => 1,
                    'nama_lapangan' => 'Lapangan 1 (Dekat Pintu Masuk)',
                    'harga_per_jam' => 35000,
                    'deskripsi' => 'Pencahayaan terang LED, lantai vinyl empuk standar PBSI.',
                    'status' => 'tersedia',
                ],
                (object)[
                    'id' => 2,
                    'nomor_lapangan' => 2,
                    'nama_lapangan' => 'Lapangan 2',
                    'harga_per_jam' => 35000,
                    'deskripsi' => 'Lantai vinyl kualitas tinggi, sirkulasi udara baik.',
                    'status' => 'tersedia',
                ],
                (object)[
                    'id' => 3,
                    'nomor_lapangan' => 3,
                    'nama_lapangan' => 'Lapangan 3 (Tengah)',
                    'harga_per_jam' => 35000,
                    'deskripsi' => 'Posisi strategis di tengah, cocok untuk turnamen lokal.',
                    'status' => 'tersedia',
                ],
                (object)[
                    'id' => 4,
                    'nomor_lapangan' => 4,
                    'nama_lapangan' => 'Lapangan 4',
                    'harga_per_jam' => 35000,
                    'deskripsi' => 'Lapangan bersih dan terawat dengan garis lapangan standar.',
                    'status' => 'tersedia',
                ],
                (object)[
                    'id' => 5,
                    'nomor_lapangan' => 5,
                    'nama_lapangan' => 'Lapangan 5 (Ujung)',
                    'harga_per_jam' => 35000,
                    'deskripsi' => 'Suasana lebih privat dengan pencahayaan optimal.',
                    'status' => 'tersedia',
                ],
            ]);
        }

        return view('home', compact('lapangans'));
    }
}
