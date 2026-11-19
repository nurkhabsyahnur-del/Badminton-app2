@extends('layouts.app')

@section('title', 'Riwayat Pemesanan Saya')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-receipt text-primary me-2"></i> Riwayat Pemesanan
            </h3>
            <p class="text-muted small mb-0">Daftar semua jadwal lapangan yang pernah Anda sewa di GOR Metro Badminton.</p>
        </div>
        <a href="{{ route('booking.index') }}" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-plus-circle me-1"></i> Sewa Baru
        </a>
    </div>

    <div class="card card-custom bg-white p-4">
        @if($pemesanans->isEmpty())
            <div class="text-center py-5">
                <div class="text-muted mb-3" style="font-size: 3rem;"><i class="bi bi-calendar-x"></i></div>
                <h5 class="fw-bold text-dark">Belum Ada Pemesanan</h5>
                <p class="small text-muted mb-3">Anda belum pernah melakukan pemesanan lapangan.</p>
                <a href="{{ route('booking.index') }}" class="btn btn-primary rounded-pill px-4">Pesan Sekarang</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Kode Booking</th>
                            <th>Lapangan</th>
                            <th>Tanggal Main</th>
                            <th>Jam Bermain</th>
                            <th>Total Biaya</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pemesanans as $p)
                            <tr>
                                <td class="fw-bold font-monospace text-primary">{{ $p->kode_booking }}</td>
                                <td>{{ $p->lapangan->nama_lapangan }}</td>
                                <td>{{ \Carbon\Carbon::parse($p->tanggal_main)->translatedFormat('d M Y') }}</td>
                                <td>{{ substr($p->jam_mulai, 0, 5) }} - {{ substr($p->jam_selesai, 0, 5) }} ({{ $p->durasi_jam }} Jam)</td>
                                <td class="fw-semibold">Rp {{ number_format($p->total_harga, 0, ',', '.') }}</td>
                                <td>
                                    @if($p->status === 'menunggu_pembayaran')
                                        <span class="badge bg-warning text-dark rounded-pill">Menunggu Pembayaran</span>
                                    @elseif($p->status === 'menunggu_konfirmasi')
                                        <span class="badge bg-info text-dark rounded-pill">Menunggu Verifikasi</span>
                                    @elseif($p->status === 'terkonfirmasi')
                                        <span class="badge bg-success rounded-pill">Terkonfirmasi</span>
                                    @elseif($p->status === 'dibatalkan')
                                        <span class="badge bg-danger rounded-pill">Dibatalkan</span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill">{{ ucfirst($p->status) }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('booking.detail', $p->kode_booking) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3" title="Lihat Detail">
                                            Detail
                                        </a>
                                        @if($p->status === 'terkonfirmasi')
                                            <a href="{{ route('booking.cetak', $p->kode_booking) }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3" title="Cetak Kwitansi">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $pemesanans->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
