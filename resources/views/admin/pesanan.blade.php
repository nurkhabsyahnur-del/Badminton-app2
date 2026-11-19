@extends('layouts.admin')

@section('title', 'Kelola Pesanan Lapangan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Kelola Pesanan Lapangan</h3>
        <p class="text-secondary small mb-0">Verifikasi bukti transfer dan kelola status pemesanan pelanggan.</p>
    </div>
</div>

<!-- Filter Status Tabs -->
<div class="card stat-card bg-white p-3 mb-4">
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('admin.pesanan') }}" class="btn btn-sm {{ !$status ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3">
            Semua Pesanan
        </a>
        <a href="{{ route('admin.pesanan', ['status' => 'menunggu_konfirmasi']) }}" class="btn btn-sm {{ $status === 'menunggu_konfirmasi' ? 'btn-info text-white' : 'btn-outline-secondary' }} rounded-pill px-3">
            <i class="bi bi-hourglass-split me-1"></i> Menunggu Konfirmasi
        </a>
        <a href="{{ route('admin.pesanan', ['status' => 'menunggu_pembayaran']) }}" class="btn btn-sm {{ $status === 'menunggu_pembayaran' ? 'btn-warning text-dark' : 'btn-outline-secondary' }} rounded-pill px-3">
            <i class="bi bi-clock me-1"></i> Menunggu Pembayaran
        </a>
        <a href="{{ route('admin.pesanan', ['status' => 'terkonfirmasi']) }}" class="btn btn-sm {{ $status === 'terkonfirmasi' ? 'btn-success' : 'btn-outline-secondary' }} rounded-pill px-3">
            <i class="bi bi-check-circle-fill me-1"></i> Terkonfirmasi
        </a>
        <a href="{{ route('admin.pesanan', ['status' => 'dibatalkan']) }}" class="btn btn-sm {{ $status === 'dibatalkan' ? 'btn-danger' : 'btn-outline-secondary' }} rounded-pill px-3">
            <i class="bi bi-x-circle me-1"></i> Dibatalkan
        </a>
    </div>
</div>

<!-- Tabel Pesanan (Sesuai Skripsi Kating: Gambar 26 & 35) -->
<div class="card stat-card bg-white p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Kode Booking</th>
                    <th>Penyewa</th>
                    <th>Lapangan</th>
                    <th>Tanggal Main</th>
                    <th>Jam Main</th>
                    <th>Total Biaya</th>
                    <th>Status</th>
                    <th>Bukti Bayar</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pesanans as $p)
                    <tr>
                        <td class="fw-bold font-monospace text-primary">{{ $p->kode_booking }}</td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $p->user->name }}</div>
                            <small class="text-muted"><i class="bi bi-whatsapp text-success me-1"></i>{{ $p->user->no_hp }}</small>
                        </td>
                        <td>Lapangan {{ $p->lapangan->nomor_lapangan }}</td>
                        <td>{{ \Carbon\Carbon::parse($p->tanggal_main)->format('d/m/Y') }}</td>
                        <td>{{ substr($p->jam_mulai, 0, 5) }} - {{ substr($p->jam_selesai, 0, 5) }} ({{ $p->durasi_jam }} jam)</td>
                        <td class="fw-bold text-success">Rp {{ number_format($p->total_harga, 0, ',', '.') }}</td>
                        <td>
                            @if($p->status === 'menunggu_pembayaran')
                                <span class="badge bg-warning text-dark rounded-pill">Menunggu Bayar</span>
                            @elseif($p->status === 'menunggu_konfirmasi')
                                <span class="badge bg-info text-dark rounded-pill">Perlu Dicek</span>
                            @elseif($p->status === 'terkonfirmasi')
                                <span class="badge bg-success rounded-pill">Terkonfirmasi</span>
                            @elseif($p->status === 'dibatalkan')
                                <span class="badge bg-danger rounded-pill">Dibatalkan</span>
                            @endif
                        </td>
                        <td>
                            @if($p->pembayaran && $p->pembayaran->bukti_transfer)
                                <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalBukti{{ $p->id }}">
                                    <i class="bi bi-image me-1"></i> Lihat Foto
                                </button>

                                <!-- Modal Bukti Transfer -->
                                <div class="modal fade" id="modalBukti{{ $p->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h6 class="modal-title fw-bold">Bukti Pembayaran - {{ $p->kode_booking }}</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-center bg-light">
                                                <div class="mb-2">
                                                    <span class="badge {{ $p->pembayaran->metode_pembayaran === 'qris' ? 'bg-danger' : 'bg-primary' }}">
                                                        {{ $p->pembayaran->metode_pembayaran === 'qris' ? 'QRIS' : 'Transfer Bank' }}
                                                    </span>
                                                </div>
                                                <p class="small text-muted mb-2">
                                                    Pengirim: <strong>{{ $p->pembayaran->nama_pengirim }}</strong> ({{ $p->pembayaran->bank_pengirim }})
                                                </p>
                                                <img src="{{ $p->pembayaran->bukti_transfer_url }}" class="img-fluid rounded border shadow-sm" style="max-height: 400px;">
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted small">- Belum ada -</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                @if($p->status === 'menunggu_konfirmasi')
                                    <form action="{{ route('admin.pesanan.konfirmasi', $p->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3" title="Konfirmasi Pembayaran">
                                            <i class="bi bi-check-lg me-1"></i> Konfirmasi
                                        </button>
                                    </form>
                                @endif

                                @if(in_array($p->status, ['menunggu_pembayaran', 'menunggu_konfirmasi']))
                                    <form action="{{ route('admin.pesanan.batalkan', $p->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3" title="Batalkan">
                                            <i class="bi bi-x-lg me-1"></i> Batalkan
                                        </button>
                                    </form>
                                @endif

                                @if($p->status === 'terkonfirmasi')
                                    <a href="{{ route('booking.cetak', $p->kode_booking) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3" title="Cetak Struk">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            Tidak ada data pemesanan yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $pesanans->links() }}
    </div>
</div>
@endsection
