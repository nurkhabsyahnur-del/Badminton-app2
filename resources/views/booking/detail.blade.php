@extends('layouts.app')

@section('title', 'Detail Pemesanan & Pembayaran')

@section('content')
<div class="container py-4">
    <div class="row g-4">
        <!-- Rincian Pesanan -->
        <div class="col-lg-7">
            <div class="card card-custom bg-white p-4 p-md-5 h-100">
                <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
                    <div>
                        <span class="text-muted small">Kode Pemesanan</span>
                        <h4 class="fw-bold text-primary mb-0">{{ $pemesanan->kode_booking }}</h4>
                    </div>
                    <div>
                        @if($pemesanan->status === 'menunggu_pembayaran')
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="bi bi-clock me-1"></i> Menunggu Pembayaran</span>
                        @elseif($pemesanan->status === 'menunggu_konfirmasi')
                            <span class="badge bg-info text-dark px-3 py-2 rounded-pill"><i class="bi bi-hourglass-split me-1"></i> Menunggu Verifikasi Admin</span>
                        @elseif($pemesanan->status === 'terkonfirmasi')
                            <span class="badge bg-success px-3 py-2 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i> Terkonfirmasi</span>
                        @elseif($pemesanan->status === 'dibatalkan')
                            <span class="badge bg-danger px-3 py-2 rounded-pill"><i class="bi bi-x-circle-fill me-1"></i> Dibatalkan</span>
                        @else
                            <span class="badge bg-secondary px-3 py-2 rounded-pill">{{ ucfirst($pemesanan->status) }}</span>
                        @endif
                    </div>
                </div>

                <!-- Timer 5 Menit Auto-Cancel -->
                @if($pemesanan->status === 'menunggu_pembayaran')
                    <div class="alert alert-warning border-warning d-flex align-items-center gap-3 p-3 rounded-3 mb-4" id="countdownBox">
                        <i class="bi bi-stopwatch fs-2 text-warning"></i>
                        <div>
                            <div class="fw-bold text-dark">Batas Waktu Pembayaran (5 Menit)</div>
                            <small class="text-secondary">Selesaikan pembayaran sebelum slot pesanan dibatalkan otomatis:</small>
                            <div class="fs-5 fw-bold text-danger mt-1" id="timer">--:--:--</div>
                        </div>
                    </div>
                @endif

                <h5 class="fw-bold text-dark mb-3">Rincian Lapangan & Waktu Main</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td class="text-muted ps-0" style="width: 35%;">Lapangan:</td>
                                <td class="fw-semibold">{{ $pemesanan->lapangan->nama_lapangan }} (Lapangan {{ $pemesanan->lapangan->nomor_lapangan }})</td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Tanggal Bermain:</td>
                                <td class="fw-semibold">{{ \Carbon\Carbon::parse($pemesanan->tanggal_main)->translatedFormat('l, d F Y') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Jam Main:</td>
                                <td class="fw-semibold">{{ substr($pemesanan->jam_mulai, 0, 5) }} - {{ substr($pemesanan->jam_selesai, 0, 5) }} WIB ({{ $pemesanan->durasi_jam }} Jam)</td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Tarif per Jam:</td>
                                <td>Rp {{ number_format($pemesanan->lapangan->harga_per_jam, 0, ',', '.') }}</td>
                            </tr>
                            @if($pemesanan->catatan)
                                <tr>
                                    <td class="text-muted ps-0">Catatan:</td>
                                    <td>{{ $pemesanan->catatan }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center mt-auto border">
                    <span class="fw-bold text-secondary">Total Tagihan:</span>
                    <span class="fs-3 fw-bold text-success">Rp {{ number_format($pemesanan->total_harga, 0, ',', '.') }}</span>
                </div>

                @if($pemesanan->status === 'terkonfirmasi')
                    <div class="mt-4">
                        <a href="{{ route('booking.cetak', $pemesanan->kode_booking) }}" target="_blank" class="btn btn-outline-primary rounded-pill w-100">
                            <i class="bi bi-printer me-2"></i> Cetak Kwitansi / E-Ticket
                        </a>
                    </div>
                @endif

                @if($pemesanan->status === 'menunggu_pembayaran')
                    <div class="mt-4 text-center">
                        <form action="{{ route('booking.batal', $pemesanan->kode_booking) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')">
                            @csrf
                            <button type="submit" class="btn btn-link text-danger text-decoration-none small">
                                <i class="bi bi-x-circle me-1"></i> Batalkan Pesanan Ini
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <!-- Instruksi Pembayaran & Upload Bukti -->
        <div class="col-lg-5">
            <div class="card card-custom bg-white p-4 p-md-5 h-100">
                <h5 class="fw-bold text-dark mb-2">Instruksi Pembayaran</h5>
                <p class="small text-muted mb-3">
                    Pilih metode pembayaran dan bayar tepat <strong>Rp {{ number_format($pemesanan->total_harga, 0, ',', '.') }}</strong> sebelum batas waktu berakhir.
                </p>

                <!-- Pilihan Metode: QRIS & Transfer Bank -->
                <ul class="nav nav-pills nav-fill mb-3 p-1 bg-light rounded-pill" id="pembayaranTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill fw-semibold py-2" id="tab-qris" data-bs-toggle="pill" data-bs-target="#content-qris" type="button" role="tab" onclick="setMetode('qris')">
                            <i class="bi bi-qr-code me-1"></i> QRIS (Instan)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-semibold py-2" id="tab-bank" data-bs-toggle="pill" data-bs-target="#content-bank" type="button" role="tab" onclick="setMetode('transfer_bank')">
                            <i class="bi bi-bank me-1"></i> Transfer Bank
                        </button>
                    </li>
                </ul>

                <div class="tab-content mb-4" id="pembayaranTabContent">
                    <!-- Tab Content QRIS -->
                    <div class="tab-pane fade show active" id="content-qris" role="tabpanel">
                        <div class="p-3 border rounded-3 bg-light text-center">
                            <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                                <span class="badge bg-danger px-2 py-1">QRIS NASIONAL</span>
                                <small class="text-muted fw-semibold">GOR METRO BADMINTON</small>
                            </div>
                            
                            <div class="bg-white p-3 rounded-3 d-inline-block shadow-sm border my-2">
                                <img src="{{ asset('images/qris.png') }}" alt="QRIS GOR Metro Badminton" class="img-fluid" style="width: 220px; height: 220px; object-fit: contain;">
                            </div>

                            <div class="mt-2 text-center">
                                <span class="badge bg-success-subtle text-success border border-success-subtle small px-3 py-1 rounded-pill mb-2">
                                    <i class="bi bi-check2-circle me-1"></i>Mendukung Semua Bank & E-Wallet
                                </span>
                                <p class="small text-muted mb-0" style="font-size: 0.78rem;">
                                    BCA, Mandiri, BRI, BNI, GoPay, OVO, DANA, ShopeePay, LinkAja, dll.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Content Transfer Bank -->
                    <div class="tab-pane fade" id="content-bank" role="tabpanel">
                        <div class="d-flex flex-column gap-3">
                            @foreach($rekeningList as $rek)
                                <div class="p-3 border rounded-3 bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-primary">{{ $rek->nama_bank }}</span>
                                        <small class="text-muted">a/n {{ $rek->atas_nama }}</small>
                                    </div>
                                    <div class="fs-5 fw-bold text-dark font-monospace copyable" title="Klik untuk salin">
                                        {{ $rek->nomor_rekening }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                @if($pemesanan->status === 'menunggu_pembayaran')
                    <!-- Form Upload Bukti Transfer -->
                    <div class="border-top pt-4">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-upload me-2 text-primary"></i>Unggah Bukti Pembayaran</h6>
                        <form action="{{ route('booking.upload', $pemesanan->kode_booking) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <input type="hidden" name="metode_pembayaran" id="inputMetodePembayaran" value="qris">

                            <div class="mb-3" id="formGroupRekening" style="display: none;">
                                <label class="form-label small fw-semibold text-secondary">Ditransfer ke Rekening</label>
                                <select name="rekening_id" id="selectRekening" class="form-select bg-light">
                                    <option value="">-- Pilih Rekening Tujuan --</option>
                                    @foreach($rekeningList as $rek)
                                        <option value="{{ $rek->id }}">{{ $rek->nama_bank }} - {{ $rek->nomor_rekening }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary" id="labelNamaPengirim">Nama Akun / Pengirim</label>
                                <input type="text" name="nama_pengirim" class="form-control bg-light" placeholder="Contoh: Budi Santoso" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary" id="labelBankPengirim">Aplikasi / Bank Pengirim</label>
                                <input type="text" name="bank_pengirim" class="form-control bg-light" placeholder="Contoh: GoPay, DANA, BCA, BRImo" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-semibold text-secondary" id="labelBukti">Upload Bukti Transfer / Screenshot QRIS (Max 2MB)</label>
                                <input type="file" name="bukti_transfer" class="form-control bg-light" accept="image/*" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-semibold">
                                <i class="bi bi-send-check me-1"></i> Kirim Bukti Pembayaran
                            </button>
                        </form>
                    </div>
                @elseif($pemesanan->pembayaran)
                    <!-- Tampilkan Bukti Yang Sudah Diunggah -->
                    <div class="border-top pt-4">
                        <h6 class="fw-bold text-dark mb-2">Bukti Pembayaran Terkirim</h6>
                        <div class="mb-2">
                            <span class="badge {{ $pemesanan->pembayaran->metode_pembayaran === 'qris' ? 'bg-danger' : 'bg-primary' }}">
                                {{ $pemesanan->pembayaran->metode_pembayaran === 'qris' ? 'QRIS' : 'Transfer Bank' }}
                            </span>
                            <small class="text-muted ms-2">Pengirim: <strong>{{ $pemesanan->pembayaran->nama_pengirim }}</strong> ({{ $pemesanan->pembayaran->bank_pengirim }})</small>
                        </div>
                        <p class="small text-muted mb-3">Diunggah pada: {{ $pemesanan->pembayaran->tanggal_bayar?->format('d M Y, H:i') }} WIB</p>
                        <div class="rounded-3 overflow-hidden border p-1 bg-light text-center">
                            <img src="{{ $pemesanan->pembayaran->bukti_transfer_url }}" alt="Bukti Transfer" class="img-fluid rounded" style="max-height: 250px;">
                        </div>
                    </div>
                @elseif($pemesanan->status === 'dibatalkan')
                    <div class="border-top pt-4 text-center">
                        <div class="p-4 rounded-3 bg-danger-subtle border border-danger-subtle mb-3">
                            <i class="bi bi-x-circle text-danger fs-1 mb-2 d-block"></i>
                            <h6 class="fw-bold text-danger mb-1">Pesanan Dibatalkan</h6>
                            <p class="small text-muted mb-0">
                                Batas waktu pembayaran 5 menit telah habis. Slot jadwal lapangan ini otomatis dibatalkan dan tersedia kembali untuk umum.
                            </p>
                        </div>
                        <a href="{{ route('booking.index') }}" class="btn btn-outline-primary rounded-pill px-4">
                            <i class="bi bi-calendar3 me-1"></i> Pesan Jadwal Baru
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function setMetode(metode) {
    const inputMetode = document.getElementById('inputMetodePembayaran');
    const formGroupRek = document.getElementById('formGroupRekening');
    const selectRek = document.getElementById('selectRekening');
    const labelNama = document.getElementById('labelNamaPengirim');
    const labelBank = document.getElementById('labelBankPengirim');
    const labelBukti = document.getElementById('labelBukti');

    if (inputMetode) inputMetode.value = metode;

    if (metode === 'qris') {
        if (formGroupRek) formGroupRek.style.display = 'none';
        if (selectRek) selectRek.removeAttribute('required');
        if (labelNama) labelNama.innerText = 'Nama Akun / Pengirim E-Wallet / Bank';
        if (labelBank) labelBank.innerText = 'Aplikasi Pembayaran (GoPay/DANA/BCA/dll)';
        if (labelBukti) labelBukti.innerText = 'Upload Screenshot Bukti Bayar QRIS (Max 2MB)';
    } else {
        if (formGroupRek) formGroupRek.style.display = 'block';
        if (selectRek) selectRek.setAttribute('required', 'required');
        if (labelNama) labelNama.innerText = 'Nama Pengirim (Sesuai Buku Tabungan)';
        if (labelBank) labelBank.innerText = 'Bank Pengirim (BCA/Mandiri/BRI/dll)';
        if (labelBukti) labelBukti.innerText = 'Upload Foto Struk / Bukti Transfer (Max 2MB)';
    }
}
</script>

@if($pemesanan->status === 'menunggu_pembayaran')
<script>
    // Kirim deadline sebagai Unix timestamp (milidetik) agar tidak ada masalah timezone
    const deadline = {{ $pemesanan->batas_pembayaran->timestamp }} * 1000;

    const timerEl  = document.getElementById('timer');
    const boxEl    = document.getElementById('countdownBox');

    const x = setInterval(function() {
        const now      = Date.now();
        const distance = deadline - now;

        if (distance <= 0) {
            clearInterval(x);
            timerEl.innerHTML = '<span class="text-danger fw-bold">WAKTU HABIS — Pesanan Dibatalkan</span>';
            if (boxEl) boxEl.classList.replace('alert-warning', 'alert-danger');
            setTimeout(function() { location.reload(); }, 2500);
            return;
        }

        const minutes = Math.floor(distance / 60000);
        const seconds = Math.floor((distance % 60000) / 1000);

        timerEl.innerHTML =
            (minutes < 10 ? '0' + minutes : minutes) + ' Menit ' +
            (seconds < 10 ? '0' + seconds : seconds) + ' Detik';
    }, 1000);
</script>
@endif
@endpush
