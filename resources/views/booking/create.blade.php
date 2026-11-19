@extends('layouts.app')

@section('title', 'Form Pemesanan Lapangan')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center gap-2 mb-3">
                <a href="{{ route('booking.index') }}" class="btn btn-outline-secondary btn-sm rounded-circle">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h4 class="fw-bold text-dark mb-0">Form Pemesanan Lapangan</h4>
            </div>

            <div class="card card-custom bg-white p-4 p-md-5 mb-4">
                <!-- Info Lapangan Terpilih -->
                <div class="p-3 bg-light rounded-3 mb-4 border d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <span class="badge bg-primary mb-1">Lapangan {{ $lapangan->nomor_lapangan }}</span>
                        <h5 class="fw-bold text-dark mb-1">{{ $lapangan->nama_lapangan }}</h5>
                        <small class="text-muted"><i class="bi bi-shield-check text-success me-1"></i> {{ $lapangan->jenis_karpet }}</small>
                    </div>
                    <div class="text-md-end">
                        <small class="text-muted d-block">Tarif Sewa</small>
                        <span class="fs-4 fw-bold text-primary">Rp {{ number_format($lapangan->harga_per_jam, 0, ',', '.') }}</span>
                        <small class="text-muted">/ jam</small>
                    </div>
                </div>

                <form action="{{ route('booking.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="lapangan_id" value="{{ $lapangan->id }}">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Tanggal Bermain</label>
                        <input type="date" name="tanggal_main" class="form-control bg-light @error('tanggal_main') is-invalid @enderror" value="{{ old('tanggal_main', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required>
                        @error('tanggal_main')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Jam Mulai</label>
                            <select name="jam_mulai" id="jam_mulai" class="form-select bg-light @error('jam_mulai') is-invalid @enderror" required onchange="hitungTotal()">
                                @for($hour = 7; $hour <= 22; $hour++)
                                    @php $val = sprintf('%02d:00', $hour); @endphp
                                    <option value="{{ $val }}" {{ old('jam_mulai', '16:00') == $val ? 'selected' : '' }}>
                                        {{ $val }} WIB
                                    </option>
                                @endfor
                            </select>
                            @error('jam_mulai')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Durasi Sewa</label>
                            <select name="durasi_jam" id="durasi_jam" class="form-select bg-light @error('durasi_jam') is-invalid @enderror" required onchange="hitungTotal()">
                                <option value="1" {{ old('durasi_jam') == '1' ? 'selected' : '' }}>1 Jam</option>
                                <option value="2" {{ old('durasi_jam', '2') == '2' ? 'selected' : '' }}>2 Jam</option>
                                <option value="3" {{ old('durasi_jam') == '3' ? 'selected' : '' }}>3 Jam</option>
                                <option value="4" {{ old('durasi_jam') == '4' ? 'selected' : '' }}>4 Jam</option>
                            </select>
                            @error('durasi_jam')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-secondary">Catatan Tambahan (Opsional)</label>
                        <textarea name="catatan" class="form-control bg-light" rows="2" placeholder="Contoh: Sewa raket, beli shuttlecock, dsb.">{{ old('catatan') }}</textarea>
                    </div>

                    <!-- Ringkasan Biaya -->
                    <div class="p-4 rounded-3 border bg-light mb-4">
                        <div class="d-flex justify-content-between mb-2 small text-secondary">
                            <span>Tarif per jam:</span>
                            <span class="fw-semibold">Rp {{ number_format($lapangan->harga_per_jam, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 small text-secondary">
                            <span>Durasi sewa:</span>
                            <span class="fw-semibold" id="labelDurasi">2 Jam</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark">Total Pembayaran:</span>
                            <span class="fs-4 fw-bold text-success" id="labelTotal">Rp 70.000</span>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-semibold">
                            Lanjut ke Pembayaran <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const hargaPerJam = {{ $lapangan->harga_per_jam }};

    function hitungTotal() {
        const durasi = parseInt(document.getElementById('durasi_jam').value) || 1;
        const total = hargaPerJam * durasi;
        
        document.getElementById('labelDurasi').innerText = durasi + ' Jam';
        document.getElementById('labelTotal').innerText = 'Rp ' + total.toLocaleString('id-ID');
    }

    document.addEventListener('DOMContentLoaded', hitungTotal);
</script>
@endpush
