@extends('layouts.app')

@section('title', 'Cek Jadwal & Ketersediaan Lapangan')

@section('content')
<div class="container py-4">
    <div class="row align-items-center mb-4">
        <div class="col-md-7">
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-calendar3 text-primary me-2"></i> Jadwal Ketersediaan Lapangan
            </h3>
            <p class="text-muted small mb-0">
                Pilih tanggal dan nomor lapangan untuk memeriksa slot jam yang masih kosong secara real-time.
            </p>
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0">
            <a href="{{ route('booking.create', $selectedLapanganId ?? 1) }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="bi bi-calendar-plus me-1"></i> Buat Pemesanan Baru
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card card-custom p-4 bg-white mb-4">
        <form method="GET" action="{{ route('booking.index') }}" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-semibold text-secondary">Pilih Lapangan</label>
                <select name="lapangan_id" class="form-select bg-light" onchange="this.form.submit()">
                    @foreach($lapangans as $lap)
                        <option value="{{ $lap->id }}" {{ $selectedLapanganId == $lap->id ? 'selected' : '' }}>
                            Lapangan {{ $lap->nomor_lapangan }} - {{ $lap->nama_lapangan }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label small fw-semibold text-secondary">Tanggal Bermain</label>
                <input type="date" name="tanggal" class="form-control bg-light" value="{{ $selectedDate }}" min="{{ date('Y-m-d') }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                </button>
            </div>
        </form>
    </div>

    <!-- Status Informasi Jadwal Terisi -->
    <div class="card card-custom p-4 bg-white mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold text-dark mb-0">
                Status Keterisian: <span class="text-primary">{{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l, d F Y') }}</span>
            </h5>
            <div class="d-flex gap-3 small">
                <span class="d-flex align-items-center gap-1"><span class="badge bg-success rounded-circle p-1">&nbsp;</span> Tersedia</span>
                <span class="d-flex align-items-center gap-1"><span class="badge bg-danger rounded-circle p-1">&nbsp;</span> Terbooking / Terisi</span>
                <span class="d-flex align-items-center gap-1"><span class="badge bg-warning rounded-circle p-1">&nbsp;</span> Menunggu Pembayaran</span>
            </div>
        </div>

        @if($bookings->isEmpty())
            <div class="alert alert-success d-flex align-items-center gap-2 mb-4" role="alert">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div>
                    <strong>Kabar baik!</strong> Belum ada pemesanan pada lapangan dan tanggal ini. Semua slot jam dari 07:00 hingga 23:00 masih terbuka bebas.
                </div>
            </div>
        @else
            <div class="table-responsive mb-4">
                <table class="table table-hover align-middle border">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 15%;">Jam Bermain</th>
                            <th style="width: 15%;">Durasi</th>
                            <th style="width: 30%;">Status Slot</th>
                            <th style="width: 40%;">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $b)
                            <tr>
                                <td class="fw-bold">
                                    <i class="bi bi-clock me-1 text-secondary"></i>
                                    {{ substr($b->jam_mulai, 0, 5) }} - {{ substr($b->jam_selesai, 0, 5) }}
                                </td>
                                <td>{{ $b->durasi_jam }} Jam</td>
                                <td>
                                    @if($b->status === 'terkonfirmasi')
                                        <span class="badge bg-danger px-3 py-2 rounded-pill"><i class="bi bi-lock-fill me-1"></i> Terbooking (Penuh)</span>
                                    @elseif($b->status === 'menunggu_konfirmasi')
                                        <span class="badge bg-info text-dark px-3 py-2 rounded-pill"><i class="bi bi-hourglass-split me-1"></i> Sedang Diverifikasi</span>
                                    @else
                                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="bi bi-clock me-1"></i> Menunggu Bayar</span>
                                    @endif
                                </td>
                                <td class="text-muted small">
                                    Slot jam ini sudah diamankan oleh penyewa lain.
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Slot Grid Generator (07:00 - 23:00) -->
        <h6 class="fw-bold text-secondary mb-3">Tinjauan Jam Operasional Lapangan:</h6>
        <div class="row g-2">
            @for($hour = 7; $hour <= 22; $hour++)
                @php
                    $slotStart = sprintf('%02d:00:00', $hour);
                    $slotEnd = sprintf('%02d:00:00', $hour + 1);
                    
                    $isBooked = $bookings->first(function ($item) use ($slotStart, $slotEnd) {
                        return $item->jam_mulai < $slotEnd && $item->jam_selesai > $slotStart;
                    });
                @endphp
                <div class="col-6 col-md-3 col-lg-2">
                    <div class="p-3 text-center rounded-3 border {{ $isBooked ? 'bg-danger-subtle border-danger text-danger' : 'bg-success-subtle border-success text-success' }}">
                        <div class="fw-bold">{{ sprintf('%02d:00', $hour) }} - {{ sprintf('%02d:00', $hour + 1) }}</div>
                        <small class="d-block mt-1">
                            @if($isBooked)
                                <i class="bi bi-x-circle-fill"></i> Terisi
                            @else
                                <i class="bi bi-check-circle-fill"></i> Kosong
                            @endif
                        </small>
                    </div>
                </div>
            @endfor
        </div>

        <div class="text-center mt-4 pt-3 border-top">
            <a href="{{ route('booking.create', $selectedLapanganId) }}" class="btn btn-primary btn-lg rounded-pill px-5">
                <i class="bi bi-calendar-check me-2"></i> Pesan Jadwal Sekarang
            </a>
        </div>
    </div>
</div>
@endsection
