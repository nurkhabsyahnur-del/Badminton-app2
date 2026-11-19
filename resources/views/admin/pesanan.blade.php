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
<div class="card bg-white border-0 shadow-sm p-3 mb-4 rounded-3">
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
<div class="card bg-white border-0 shadow-sm p-4 rounded-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
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
                                <button type="button" 
                                    class="btn btn-sm btn-outline-info rounded-pill px-3" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalBukti"
                                    data-kode="{{ $p->kode_booking }}"
                                    data-metode="{{ $p->pembayaran->metode_pembayaran }}"
                                    data-pengirim="{{ $p->pembayaran->nama_pengirim ?? '-' }}"
                                    data-bank="{{ $p->pembayaran->bank_pengirim ?? '-' }}"
                                    data-foto="{{ $p->pembayaran->bukti_transfer_url }}">
                                    <i class="bi bi-image me-1"></i> Lihat Foto
                                </button>
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

<!-- ============================================================== -->
<!-- MODAL BUKTI TRANSFER (Ditempatkan di luar tabel untuk mencegah flickering) -->
<!-- ============================================================== -->
<div class="modal fade" id="modalBukti" tabindex="-1" aria-labelledby="modalBuktiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0 rounded-4">
            <div class="modal-header border-bottom pb-3">
                <h6 class="modal-title fw-bold" id="modalBuktiLabel">
                    Bukti Pembayaran - <span id="bukti_kode" class="font-monospace text-primary"></span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center bg-light py-3">
                <div class="mb-2">
                    <span id="bukti_metode_badge" class="badge"></span>
                </div>
                <p class="small text-muted mb-3">
                    Pengirim: <strong id="bukti_pengirim" class="text-dark"></strong> (<span id="bukti_bank"></span>)
                </p>
                <div class="d-flex justify-content-center">
                    <img id="bukti_img" src="" alt="Bukti Transfer" class="img-fluid rounded border shadow-sm" style="max-height: 420px; object-fit: contain;">
                </div>
            </div>
            <div class="modal-footer border-top pt-2">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalBukti = document.getElementById('modalBukti');
        if (modalBukti) {
            modalBukti.addEventListener('show.bs.modal', function (event) {
                const btn = event.relatedTarget;
                if (!btn) return;

                const kode = btn.getAttribute('data-kode') || '';
                const metode = (btn.getAttribute('data-metode') || '').toLowerCase();
                const pengirim = btn.getAttribute('data-pengirim') || '-';
                const bank = btn.getAttribute('data-bank') || '-';
                const foto = btn.getAttribute('data-foto') || '';

                document.getElementById('bukti_kode').textContent = kode;
                document.getElementById('bukti_pengirim').textContent = pengirim;
                document.getElementById('bukti_bank').textContent = bank;
                document.getElementById('bukti_img').src = foto;

                const badge = document.getElementById('bukti_metode_badge');
                if (metode === 'qris') {
                    badge.className = 'badge bg-danger rounded-pill px-3 py-1';
                    badge.textContent = 'QRIS';
                } else {
                    badge.className = 'badge bg-primary rounded-pill px-3 py-1';
                    badge.textContent = 'Transfer Bank';
                }
            });
        }
    });
</script>
@endpush
