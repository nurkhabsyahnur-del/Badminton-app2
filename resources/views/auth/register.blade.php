@extends('layouts.app')

@section('title', 'Daftar Akun Baru')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card card-custom p-4 p-md-5 bg-white">
                <div class="text-center mb-4">
                    <span class="p-3 bg-light rounded-circle text-primary d-inline-block mb-3" style="font-size: 2rem;">
                        <i class="bi bi-person-plus"></i>
                    </span>
                    <h4 class="fw-bold text-dark">Daftar Akun Baru</h4>
                    <p class="text-muted small">Buat akun untuk memesan lapangan badminton online secara praktis.</p>
                </div>

                @if($errors->has('register_error'))
                    <div class="alert alert-danger py-2 small rounded-3 d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $errors->first('register_error') }}
                    </div>
                @endif

                <form action="{{ route('register') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control bg-light @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Contoh: Budi Santoso" required autofocus>
                        @error('name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Username</label>
                            <input type="text" name="username" class="form-control bg-light @error('username') is-invalid @enderror" value="{{ old('username') }}" placeholder="budisantoso" required>
                            @error('username')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Nomor WhatsApp / HP</label>
                            <input type="tel" name="no_hp" class="form-control bg-light @error('no_hp') is-invalid @enderror" value="{{ old('no_hp') }}" placeholder="081234567890" required>
                            @error('no_hp')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Alamat Email</label>
                        <input type="email" name="email" class="form-control bg-light @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="budi@example.com" required>
                        @error('email')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="row g-2 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Password (Min. 8 Karakter)</label>
                            <input type="password" name="password" class="form-control bg-light @error('password') is-invalid @enderror" placeholder="••••••••" required>
                            @error('password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Ulangi Password</label>
                            <input type="password" name="password_confirmation" class="form-control bg-light" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill fw-semibold mb-3">
                        Daftar Sekarang <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </form>

                <div class="text-center pt-3 border-top">
                    <p class="small text-muted mb-0">
                        Sudah punya akun? <a href="{{ route('login') }}" class="text-primary fw-semibold text-decoration-none">Masuk di sini</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
