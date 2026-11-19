@extends('layouts.admin')

@section('title', 'Manajemen Data Anggota')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Manajemen Data Anggota</h3>
        <p class="text-secondary small mb-0">Kelola informasi seluruh pengguna, pelanggan, dan pengurus GOR Metro Badminton.</p>
    </div>
    <button class="btn btn-primary rounded-pill px-3 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalTambahAnggota">
        <i class="bi bi-person-plus-fill"></i> Tambah Anggota Baru
    </button>
</div>

<!-- Notifikasi Error Validasi Form -->
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Terdapat kesalahan pengisian data:</div>
        <ul class="mb-0 ps-3 small">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Kotak Metrik Anggota -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card bg-white p-3 border-0 shadow-sm border-start border-4 border-primary">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-medium">Total Seluruh User</span>
                    <h3 class="fw-bold text-dark mb-0">{{ $totalUsers }}</h3>
                </div>
                <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle">
                    <i class="bi bi-people fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card bg-white p-3 border-0 shadow-sm border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-medium">Pelanggan / Anggota Aktif</span>
                    <h3 class="fw-bold text-success mb-0">{{ $totalPelanggan }}</h3>
                </div>
                <div class="p-3 bg-success bg-opacity-10 text-success rounded-circle">
                    <i class="bi bi-person-check fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card bg-white p-3 border-0 shadow-sm border-start border-4 border-danger">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-medium">Administrator</span>
                    <h3 class="fw-bold text-danger mb-0">{{ $totalAdmin }}</h3>
                </div>
                <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-circle">
                    <i class="bi bi-shield-lock fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Card Filter & Tabel Data Anggota -->
<div class="card stat-card bg-white border-0 shadow-sm p-4">
    <!-- Filter Pencarian & Role -->
    <form action="{{ route('admin.anggota.index') }}" method="GET" class="row g-2 align-items-center mb-4">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-light text-secondary border-end-0">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="q" class="form-control bg-light border-start-0" placeholder="Cari nama, username, email, nomor HP..." value="{{ $keyword }}">
            </div>
        </div>
        <div class="col-md-3">
            <select name="role" class="form-select bg-light">
                <option value="">Semua Peran (Role)</option>
                <option value="pelanggan" {{ $selectedRole === 'pelanggan' ? 'selected' : '' }}>Pelanggan</option>
                <option value="admin" {{ $selectedRole === 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary rounded-pill px-3">
                <i class="bi bi-filter"></i> Terapkan
            </button>
            @if(!empty($keyword) || !empty($selectedRole))
                <a href="{{ route('admin.anggota.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            @endif
        </div>
    </form>

    <!-- Tabel Data Anggota -->
    @if($users->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-person-x fs-1 text-secondary mb-2 d-block"></i>
            <h6 class="fw-bold">Tidak ada data anggota ditemukan.</h6>
            <p class="small text-muted mb-3">Coba gunakan kata kunci pencarian lain atau tambahkan anggota baru.</p>
            <button class="btn btn-outline-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#modalTambahAnggota">
                <i class="bi bi-plus-lg me-1"></i> Tambah Anggota Sekarang
            </button>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th>Pengguna</th>
                        <th>Kontak</th>
                        <th>Peran</th>
                        <th>Pemesanan</th>
                        <th>Tgl Terdaftar</th>
                        <th class="text-center" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $index => $u)
                        <tr>
                            <td class="text-center text-muted small fw-medium">
                                {{ $users->firstItem() + $index }}
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px;">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $u->name }}</div>
                                        <small class="text-muted">{{ '@' . $u->username }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small text-dark mb-1">
                                    <i class="bi bi-envelope text-secondary me-1"></i> {{ $u->email }}
                                </div>
                                <div class="small">
                                    <a href="https://wa.me/{{ preg_replace('/^0/', '62', $u->no_hp) }}" target="_blank" class="text-success text-decoration-none">
                                        <i class="bi bi-whatsapp me-1"></i> {{ $u->no_hp }}
                                    </a>
                                </div>
                            </td>
                            <td>
                                @if($u->role === 'admin')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1">
                                        <i class="bi bi-shield-fill me-1"></i> Administrator
                                    </span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">
                                        <i class="bi bi-person me-1"></i> Pelanggan
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border rounded-pill px-2 py-1">
                                    <i class="bi bi-calendar2-check text-primary me-1"></i> {{ $u->pemesanan_count }} Pesanan
                                </span>
                            </td>
                            <td class="small text-muted">
                                {{ $u->created_at ? $u->created_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $u->id }}" title="Edit Anggota">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    @if(auth()->id() !== $u->id)
                                        <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalDelete{{ $u->id }}" title="Hapus Anggota">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @else
                                        <button class="btn btn-outline-secondary disabled" title="Akun Anda (Tidak dapat dihapus)">
                                            <i class="bi bi-lock-fill"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Edit Anggota -->
                        <div class="modal fade" id="modalEdit{{ $u->id }}" tabindex="-1" aria-labelledby="modalEditLabel{{ $u->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="{{ route('admin.anggota.update', $u->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold" id="modalEditLabel{{ $u->id }}">
                                                <i class="bi bi-pencil-square text-primary me-2"></i> Edit Data Anggota
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-secondary">Nama Lengkap</label>
                                                <input type="text" name="name" class="form-control" value="{{ old('name', $u->name) }}" required>
                                            </div>
                                            <div class="row g-2 mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold text-secondary">Username</label>
                                                    <input type="text" name="username" class="form-control" value="{{ old('username', $u->username) }}" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold text-secondary">Role / Peran</label>
                                                    <select name="role" class="form-select" required>
                                                        <option value="pelanggan" {{ old('role', $u->role) === 'pelanggan' ? 'selected' : '' }}>Pelanggan</option>
                                                        <option value="admin" {{ old('role', $u->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-secondary">Alamat Email</label>
                                                <input type="email" name="email" class="form-control" value="{{ old('email', $u->email) }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-secondary">Nomor WhatsApp / HP</label>
                                                <input type="text" name="no_hp" class="form-control" value="{{ old('no_hp', $u->no_hp) }}" required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small fw-semibold text-secondary">Password Baru (Opsional)</label>
                                                <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
                                                <small class="text-muted d-block mt-1">Isi minimal 6 karakter hanya jika Anda ingin memperbarui password anggota ini.</small>
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

                        <!-- Modal Hapus Anggota -->
                        @if(auth()->id() !== $u->id)
                            <div class="modal fade" id="modalDelete{{ $u->id }}" tabindex="-1" aria-labelledby="modalDeleteLabel{{ $u->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.anggota.delete', $u->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title fw-bold" id="modalDeleteLabel{{ $u->id }}">
                                                    <i class="bi bi-exclamation-octagon-fill me-2"></i> Konfirmasi Hapus Anggota
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4 text-center">
                                                <div class="text-danger mb-3">
                                                    <i class="bi bi-trash3 fs-1"></i>
                                                </div>
                                                <h6 class="fw-bold mb-2">Apakah Anda yakin ingin menghapus anggota ini?</h6>
                                                <div class="p-3 bg-light rounded-3 text-start small mb-3">
                                                    <div><strong>Nama:</strong> {{ $u->name }}</div>
                                                    <div><strong>Username:</strong> {{ '@' . $u->username }}</div>
                                                    <div><strong>Email:</strong> {{ $u->email }}</div>
                                                    <div><strong>Total Booking:</strong> {{ $u->pemesanan_count }} pesanan</div>
                                                </div>
                                                <p class="text-danger small mb-0">
                                                    <i class="bi bi-info-circle me-1"></i> Tindakan ini tidak dapat dibatalkan. Seluruh riwayat pemesanan yang terkait dengan akun ini juga akan terhapus.
                                                </p>
                                            </div>
                                            <div class="modal-footer justify-content-center">
                                                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-danger btn-sm rounded-pill px-4">Ya, Hapus Anggota</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <small class="text-muted">
                Menampilkan data {{ $users->firstItem() }} s/d {{ $users->lastItem() }} dari {{ $users->total() }} total anggota
            </small>
            <div>
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>

<!-- Modal Tambah Anggota Baru -->
<div class="modal fade" id="modalTambahAnggota" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.anggota.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalTambahLabel">
                        <i class="bi bi-person-plus-fill text-primary me-2"></i> Tambah Anggota Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Budi Santoso" value="{{ old('name') }}" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" placeholder="Contoh: budi123" value="{{ old('username') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Peran (Role) <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required>
                                <option value="pelanggan" {{ old('role') === 'pelanggan' ? 'selected' : '' }}>Pelanggan</option>
                                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Alamat Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="Contoh: budi@gmail.com" value="{{ old('email') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Nomor WhatsApp / HP <span class="text-danger">*</span></label>
                        <input type="text" name="no_hp" class="form-control" placeholder="Contoh: 081234567890" value="{{ old('no_hp') }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-secondary">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill">Simpan Anggota</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
