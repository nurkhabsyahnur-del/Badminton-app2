@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Dashboard Admin</h3>
        <p class="text-secondary small mb-0">Ringkasan operasional dan pendapatan GOR Metro Badminton.</p>
    </div>
    <div class="text-muted small">
        <i class="bi bi-calendar3 me-1"></i> {{ date('d F Y') }}
    </div>
</div>

@if($menungguKonfirmasi > 0)
    <div class="alert alert-warning border-warning d-flex align-items-center justify-content-between p-3 rounded-3 mb-4 shadow-sm">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-bell-fill fs-4 text-warning"></i>
            <div>
                <strong>Ada {{ $menungguKonfirmasi }} pesanan menunggu konfirmasi pembayaran!</strong>
                <div class="small text-muted">Pelanggan telah mengunggah bukti transfer dan menunggu verifikasi Anda.</div>
            </div>
        </div>
        <a href="{{ route('admin.pesanan', ['status' => 'menunggu_konfirmasi']) }}" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold">
            Periksa Sekarang <i class="bi bi-arrow-right"></i>
        </a>
    </div>
@endif

<!-- 4 Kotak Statistik (Sesuai Skripsi Kating: Gambar 34 & 41) -->
<div class="row g-3 mb-4">
    <!-- 1. Pesanan Hari Ini (Biru) -->
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card text-white p-4" style="background-color: #0284c7;">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="fs-1 fw-bold">{{ $pesananHariIni }}</span>
                <i class="bi bi-calendar-event fs-3 opacity-75"></i>
            </div>
            <div class="fw-medium">Penyewaan Hari Ini</div>
            <small class="opacity-75">Total booking jadwal hari ini</small>
        </div>
    </div>

    <!-- 2. Pesanan Bulan Ini (Hijau) -->
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card text-white p-4" style="background-color: #16a34a;">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="fs-1 fw-bold">{{ $pesananBulanIni }}</span>
                <i class="bi bi-calendar-month fs-3 opacity-75"></i>
            </div>
            <div class="fw-medium">Penyewaan Bulan Ini</div>
            <small class="opacity-75">Akumulasi pesanan bulan {{ date('F') }}</small>
        </div>
    </div>

    <!-- 3. Pendapatan Hari Ini (Kuning) -->
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card text-dark p-4" style="background-color: #eab308;">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="fs-3 fw-bold mt-1">Rp {{ number_format($pendapatanHariIni, 0, ',', '.') }}</span>
                <i class="bi bi-cash-stack fs-3 opacity-75"></i>
            </div>
            <div class="fw-bold">Pendapatan Hari Ini</div>
            <small class="opacity-75">Total transaksi valid hari ini</small>
        </div>
    </div>

    <!-- 4. Pendapatan Bulan Ini (Merah) -->
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card text-white p-4" style="background-color: #dc2626;">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="fs-3 fw-bold mt-1">Rp {{ number_format($pendapatanBulanIni, 0, ',', '.') }}</span>
                <i class="bi bi-wallet2 fs-3 opacity-75"></i>
            </div>
            <div class="fw-medium">Pendapatan Bulan Ini</div>
            <small class="opacity-75">Total omset bulan {{ date('F') }}</small>
        </div>
    </div>
</div>

<!-- Tabel Jadwal Lapangan Hari Ini -->
<div class="card stat-card bg-white p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-dark mb-0">
            <i class="bi bi-clock-history text-primary me-2"></i> Jadwal Pemakaian Lapangan Hari Ini ({{ date('d/m/Y') }})
        </h5>
        <a href="{{ route('admin.pesanan') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
            Semua Pesanan <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    @if($jadwalHariIni->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-calendar-check fs-1 text-secondary mb-2 d-block"></i>
            Belum ada jadwal pemakaian lapangan yang terkonfirmasi hari ini.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Jam Main</th>
                        <th>Lapangan</th>
                        <th>Nama Penyewa</th>
                        <th>WhatsApp</th>
                        <th>Durasi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jadwalHariIni as $j)
                        <tr>
                            <td class="fw-bold text-primary">
                                {{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }} WIB
                            </td>
                            <td><span class="badge bg-light text-dark border">Lapangan {{ $j->lapangan->nomor_lapangan }}</span></td>
                            <td class="fw-semibold">{{ $j->user->name }}</td>
                            <td>
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', $j->user->no_hp) }}" target="_blank" class="text-success text-decoration-none">
                                    <i class="bi bi-whatsapp me-1"></i> {{ $j->user->no_hp }}
                                </a>
                            </td>
                            <td>{{ $j->durasi_jam }} Jam</td>
                            <td>
                                @if($j->status === 'terkonfirmasi')
                                    <span class="badge bg-success rounded-pill">Terkonfirmasi</span>
                                @else
                                    <span class="badge bg-info text-dark rounded-pill">Menunggu Verifikasi</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
