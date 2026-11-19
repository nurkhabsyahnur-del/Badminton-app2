@extends('layouts.admin')

@section('title', 'Rekapitulasi Laporan Penyewaan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Rekapitulasi Laporan Penyewaan</h3>
        <p class="text-secondary small mb-0">Laporan data transaksi dan pendapatan sewa lapangan GOR Metro Badminton.</p>
    </div>
    <div>
        <a href="{{ route('admin.rekap', array_merge(request()->all(), ['print' => 1])) }}" target="_blank" class="btn btn-danger rounded-pill px-4 shadow-sm">
            <i class="bi bi-file-earmark-pdf me-1"></i> Cetak / Unduh PDF
        </a>
    </div>
</div>

<!-- Filter Form (Sesuai Skripsi Kating: Gambar 27 & 36) -->
<div class="card stat-card bg-white p-4 mb-4">
    <form method="GET" action="{{ route('admin.rekap') }}" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-secondary">Tipe Rekap</label>
            <select name="filter_type" id="filter_type" class="form-select bg-light" onchange="toggleFilterType()">
                <option value="bulanan" {{ $filterType === 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                <option value="harian" {{ $filterType === 'harian' ? 'selected' : '' }}>Harian</option>
            </select>
        </div>

        <div class="col-md-3 filter-bulanan" style="{{ $filterType === 'harian' ? 'display:none;' : '' }}">
            <label class="form-label small fw-semibold text-secondary">Bulan</label>
            <select name="bulan" class="form-select bg-light">
                @for($m = 1; $m <= 12; $m++)
                    @php $val = sprintf('%02d', $m); @endphp
                    <option value="{{ $val }}" {{ $bulan == $val ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                @endfor
            </select>
        </div>

        <div class="col-md-3 filter-bulanan" style="{{ $filterType === 'harian' ? 'display:none;' : '' }}">
            <label class="form-label small fw-semibold text-secondary">Tahun</label>
            <select name="tahun" class="form-select bg-light">
                @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>

        <div class="col-md-6 filter-harian" style="{{ $filterType !== 'harian' ? 'display:none;' : '' }}">
            <label class="form-label small fw-semibold text-secondary">Pilih Tanggal</label>
            <input type="date" name="tanggal" class="form-control bg-light" value="{{ $tanggal }}">
        </div>

        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-filter me-1"></i> Tampilkan Rekap
            </button>
        </div>
    </form>
</div>

<!-- Ringkasan Hasil Rekap -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="p-3 bg-white rounded-3 border">
            <small class="text-muted d-block">Periode Laporan</small>
            <span class="fs-5 fw-bold text-dark">
                @if($filterType === 'harian')
                    {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}
                @else
                    {{ \Carbon\Carbon::create()->month(intval($bulan))->translatedFormat('F') }} {{ $tahun }}
                @endif
            </span>
        </div>
    </div>
    <div class="col-md-4">
        <div class="p-3 bg-white rounded-3 border">
            <small class="text-muted d-block">Total Transaksi Selesai</small>
            <span class="fs-4 fw-bold text-primary">{{ $totalPenyewaan }} Penyewaan</span>
        </div>
    </div>
    <div class="col-md-4">
        <div class="p-3 bg-white rounded-3 border">
            <small class="text-muted d-block">Total Pendapatan Bersih</small>
            <span class="fs-4 fw-bold text-success">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</span>
        </div>
    </div>
</div>

<!-- Tabel Rekap (Sesuai Skripsi Kating: Gambar 36 & 42) -->
<div class="card stat-card bg-white p-4">
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr class="text-center">
                    <th style="width: 50px;">No</th>
                    <th>Nama Penyewa</th>
                    <th>Lapangan</th>
                    <th>Tanggal Main</th>
                    <th>Jam Masuk</th>
                    <th>Durasi</th>
                    <th>Jam Selesai</th>
                    <th>Total Harga</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaksi as $index => $t)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="fw-semibold">{{ $t->user->name }}</td>
                        <td class="text-center">Lapangan {{ $t->lapangan->nomor_lapangan }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($t->tanggal_main)->format('d-m-Y') }}</td>
                        <td class="text-center">{{ substr($t->jam_mulai, 0, 5) }}</td>
                        <td class="text-center">{{ $t->durasi_jam }} Jam</td>
                        <td class="text-center">{{ substr($t->jam_selesai, 0, 5) }}</td>
                        <td class="text-end fw-bold">Rp {{ number_format($t->total_harga, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            Tidak ada data transaksi terkonfirmasi pada periode yang dipilih.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($transaksi->isNotEmpty())
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="7" class="text-end">Total Pendapatan:</td>
                        <td class="text-end text-success">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleFilterType() {
        const type = document.getElementById('filter_type').value;
        const bulananEl = document.querySelectorAll('.filter-bulanan');
        const harianEl = document.querySelectorAll('.filter-harian');

        if (type === 'harian') {
            bulananEl.forEach(el => el.style.display = 'none');
            harianEl.forEach(el => el.style.display = 'block');
        } else {
            bulananEl.forEach(el => el.style.display = 'block');
            harianEl.forEach(el => el.style.display = 'none');
        }
    }
</script>
@endpush
