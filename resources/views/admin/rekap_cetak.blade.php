<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekapitulasi Penyewaan - GOR Metro Badminton</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            background: #fff;
            padding: 20px;
        }
        .header-title {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 12px;
            margin-bottom: 25px;
        }
        .logo-circle {
            width: 70px;
            height: 70px;
            border: 3px solid #16a34a;
            color: #16a34a;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        table {
            font-size: 13px;
        }
        th, td {
            border: 1px solid #000 !important;
            padding: 6px 10px !important;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="text-center my-3 no-print">
        <button onclick="window.print()" class="btn btn-primary btn-sm px-4 rounded-pill">
            Cetak Laporan (PDF / Print)
        </button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm px-4 rounded-pill ms-2">
            Tutup
        </button>
    </div>

    <div class="container" style="max-width: 850px;">
        <!-- Header Sesuai Skripsi Kating: Gambar 37 & 43 -->
        <div class="header-title">
            <div class="logo-circle">MB</div>
            <h3 class="fw-bold mb-0">GOR Metro Badminton</h3>
            <p class="mb-0 small">Jl. Kanguru, Hadimulyo Timur, Kecamatan Metro Pusat, Kota Metro, Lampung</p>
        </div>

        <h5 class="fw-bold text-center mb-3 text-uppercase">
            @if($filterType === 'harian')
                Rekap Tanggal {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}
            @else
                Rekap Bulan {{ \Carbon\Carbon::create()->month(intval($bulan))->translatedFormat('F') }} {{ $tahun }}
            @endif
        </h5>

        <table class="table table-bordered w-100 mb-3">
            <thead class="text-center" style="background-color: #f1f5f9;">
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Nama</th>
                    <th style="width: 80px;">Lapangan</th>
                    <th style="width: 95px;">Tanggal</th>
                    <th style="width: 80px;">Jam Masuk</th>
                    <th style="width: 65px;">Durasi</th>
                    <th style="width: 80px;">Jam Selesai</th>
                    <th style="width: 110px;">Total Harga</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaksi as $index => $t)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $t->user->name }}</td>
                        <td class="text-center">{{ $t->lapangan->nomor_lapangan }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($t->tanggal_main)->format('d-m-Y') }}</td>
                        <td class="text-center">{{ substr($t->jam_mulai, 0, 5) }}</td>
                        <td class="text-center">{{ $t->durasi_jam }} Jam</td>
                        <td class="text-center">{{ substr($t->jam_selesai, 0, 5) }}</td>
                        <td class="text-end">Rp {{ number_format($t->total_harga, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-3">Tidak ada data transaksi pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-3">
            <p class="mb-1 fw-bold">Total Penyewaan: {{ $totalPenyewaan }}</p>
            <p class="mb-3 fw-bold">Total Pendapatan: Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
        </div>

        <div class="row mt-5 pt-4">
            <div class="col-8"></div>
            <div class="col-4 text-center">
                <p class="mb-1">Kota Metro, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p class="mb-5">Pengurus GOR Metro Badminton,</p>
                <br><br>
                <p class="fw-bold text-decoration-underline mb-0">Suyitno / Sutrisno</p>
                <small class="text-muted">Pengurus</small>
            </div>
        </div>
    </div>
</body>
</html>
