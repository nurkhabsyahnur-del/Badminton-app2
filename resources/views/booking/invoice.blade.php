<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Ticket / Bukti Sewa - {{ $pemesanan->kode_booking }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff;
            color: #000;
        }
        .ticket-box {
            max-width: 650px;
            margin: 20px auto;
            border: 2px dashed #0f766e;
            padding: 30px;
            border-radius: 12px;
        }
        .stamp {
            border: 3px solid #16a34a;
            color: #16a34a;
            font-weight: bold;
            font-size: 18px;
            padding: 6px 16px;
            display: inline-block;
            text-transform: uppercase;
            border-radius: 6px;
            letter-spacing: 2px;
            transform: rotate(-5deg);
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .ticket-box {
                border: 2px solid #000;
                margin: 0;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="text-center my-3 no-print">
        <button onclick="window.print()" class="btn btn-primary btn-sm px-4 rounded-pill">
            Cetak Dokumen Ini
        </button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm px-4 rounded-pill ms-2">
            Tutup
        </button>
    </div>

    <div class="ticket-box">
        <!-- Header -->
        <div class="text-center pb-3 border-bottom mb-4">
            <h3 class="fw-bold mb-1" style="color: #0f766e;">GOR METRO BADMINTON</h3>
            <p class="small mb-0 text-muted">Jl. Kanguru, Hadimulyo Timur, Kec. Metro Pusat, Kota Metro, Lampung</p>
            <p class="small mb-0 text-muted">Telp / WA: 0887-4376-41573 • Buka Pukul 07:00 - 23:00 WIB</p>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <small class="text-muted d-block">KODE BOOKING:</small>
                <h4 class="fw-bold font-monospace mb-0">{{ $pemesanan->kode_booking }}</h4>
            </div>
            <div class="text-end">
                <span class="stamp">LUNAS</span>
            </div>
        </div>

        <!-- Table Info -->
        <table class="table table-bordered mb-4">
            <tbody>
                <tr>
                    <th style="width: 35%; background: #f8fafc;">Nama Penyewa</th>
                    <td>{{ $pemesanan->user->name }}</td>
                </tr>
                <tr>
                    <th style="background: #f8fafc;">Nomor WhatsApp</th>
                    <td>{{ $pemesanan->user->no_hp }}</td>
                </tr>
                <tr>
                    <th style="background: #f8fafc;">Lapangan</th>
                    <td class="fw-bold">{{ $pemesanan->lapangan->nama_lapangan }}</td>
                </tr>
                <tr>
                    <th style="background: #f8fafc;">Tanggal Bermain</th>
                    <td class="fw-bold">{{ \Carbon\Carbon::parse($pemesanan->tanggal_main)->translatedFormat('l, d F Y') }}</td>
                </tr>
                <tr>
                    <th style="background: #f8fafc;">Jam Bermain</th>
                    <td class="fw-bold">{{ substr($pemesanan->jam_mulai, 0, 5) }} - {{ substr($pemesanan->jam_selesai, 0, 5) }} WIB ({{ $pemesanan->durasi_jam }} Jam)</td>
                </tr>
                <tr>
                    <th style="background: #f8fafc;">Metode Pembayaran</th>
                    <td>
                        <span class="badge bg-light text-dark border">
                            {{ $pemesanan->pembayaran?->metode_pembayaran === 'qris' ? 'QRIS (E-Wallet / Bank)' : 'Transfer Bank' }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th style="background: #f8fafc;">Total Pembayaran</th>
                    <td class="fw-bold fs-5 text-success">Rp {{ number_format($pemesanan->total_harga, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Footer Notice -->
        <div class="p-3 bg-light rounded text-muted small mb-4">
            <strong>Catatan & Tata Tertib:</strong>
            <ul class="mb-0 ps-3 mt-1">
                <li>Harap datang 10-15 menit sebelum jam mulai sewa.</li>
                <li>Wajib menggunakan sepatu khusus badminton ber-sol karet (*non-marking*).</li>
                <li>Tunjukkan bukti kwitansi ini ke petugas penjaga GOR Metro Badminton.</li>
            </ul>
        </div>

        <div class="d-flex justify-content-between text-muted small pt-2">
            <div>Waktu Cetak: {{ date('d/m/Y H:i') }} WIB</div>
            <div>Petugas: Admin GOR Metro</div>
        </div>
    </div>
</body>
</html>
