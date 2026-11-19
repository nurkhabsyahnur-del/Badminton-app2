@extends('layouts.admin')

@section('title', 'Data Lapangan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Manajemen Data Lapangan</h3>
        <p class="text-secondary small mb-0">Kelola tarif sewa dan ketersediaan 5 lapangan GOR Metro Badminton.</p>
    </div>
</div>

<div class="row g-4">
    @foreach($lapangans as $lap)
        <div class="col-md-6 col-xl-4">
            <div class="card stat-card bg-white h-100 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-primary">Lapangan {{ $lap->nomor_lapangan }}</span>
                    <span class="badge {{ $lap->status === 'tersedia' ? 'bg-success' : 'bg-danger' }}">
                        {{ ucfirst($lap->status) }}
                    </span>
                </div>

                <h5 class="fw-bold text-dark mb-2">{{ $lap->nama_lapangan }}</h5>
                <p class="text-muted small mb-3">{{ $lap->deskripsi }}</p>

                <div class="p-3 bg-light rounded-3 mb-4 border">
                    <small class="text-muted d-block">Tarif Saat Ini</small>
                    <div class="fs-4 fw-bold text-primary">
                        Rp {{ number_format($lap->harga_per_jam, 0, ',', '.') }} <small class="fs-6 fw-normal text-muted">/ jam</small>
                    </div>
                    <small class="text-muted"><i class="bi bi-shield-check text-success me-1"></i>{{ $lap->jenis_karpet }}</small>
                </div>

                <div class="mt-auto pt-3 border-top">
                    <button class="btn btn-outline-primary btn-sm rounded-pill w-100" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $lap->id }}">
                        <i class="bi bi-pencil-square me-1"></i> Edit Tarif & Status
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Edit Lapangan -->
        <div class="modal fade" id="modalEdit{{ $lap->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('admin.lapangan.update', $lap->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h6 class="modal-title fw-bold">Edit Lapangan {{ $lap->nomor_lapangan }}</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Nama Lapangan</label>
                                <input type="text" name="nama_lapangan" class="form-control" value="{{ $lap->nama_lapangan }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Harga Sewa Per Jam (Rp)</label>
                                <input type="number" name="harga_per_jam" class="form-control" value="{{ intval($lap->harga_per_jam) }}" min="10000" step="1000" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Status Lapangan</label>
                                <select name="status" class="form-select" required>
                                    <option value="tersedia" {{ $lap->status === 'tersedia' ? 'selected' : '' }}>Tersedia (Bisa Disewa)</option>
                                    <option value="perbaikan" {{ $lap->status === 'perbaikan' ? 'selected' : '' }}>Perbaikan / Tutup Sementara</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Deskripsi / Keterangan</label>
                                <textarea name="deskripsi" class="form-control" rows="2">{{ $lap->deskripsi }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary btn-sm rounded-pill">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
