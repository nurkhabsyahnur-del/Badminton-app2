@extends('layouts.app')

@section('title', 'Penyewaan Lapangan Badminton Terpercaya')

@section('content')

{{-- ═══════════════════════════════════════════
     HERO SECTION — Full Background Image
═══════════════════════════════════════════ --}}
<div class="hero-section position-relative overflow-hidden">

    {{-- Background Image --}}
    <div class="hero-bg"
         style="background: url('https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=1920&q=85') center center / cover no-repeat; position:absolute; inset:0; z-index:0;">
    </div>
    {{-- Dark Gradient Overlay --}}
    <div style="position:absolute; inset:0; background:linear-gradient(120deg, rgba(10,25,41,0.82) 0%, rgba(15,118,110,0.65) 60%, rgba(0,0,0,0.5) 100%); z-index:1;"></div>
    {{-- Court Grid Lines --}}
    <div style="position:absolute; inset:0; z-index:1; opacity:0.05;
         background-image: linear-gradient(white 1px, transparent 1px), linear-gradient(90deg, white 1px, transparent 1px);
         background-size: 70px 70px;">
    </div>

    {{-- Content --}}
    <div class="container position-relative" style="z-index:2; padding-top:7rem; padding-bottom:7rem;">
        <div class="row align-items-center g-5">

            {{-- LEFT: Teks --}}
            <div class="col-lg-6 text-white">
                <span class="badge px-3 py-2 rounded-pill fw-medium mb-4 d-inline-flex align-items-center gap-2"
                      style="background:rgba(255,255,255,0.15); backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,0.25); font-size:.8rem;">
                    <span class="rounded-circle bg-success d-inline-block" style="width:8px;height:8px;"></span>
                    Standar PBSI &bull; 5 Lapangan Karpet Vinyl
                </span>

                <h1 class="display-4 fw-bold lh-sm mb-4">
                    Sewa Lapangan Badminton di
                    <span style="color:#34d399;">GOR Metro</span>
                    Lebih Mudah &amp; Cepat
                </h1>
                <p class="lead mb-5 opacity-75" style="max-width:500px; line-height:1.8;">
                    Cek jadwal kosong secara <strong class="text-white">real-time</strong>, pilih nomor lapangan favorit, dan amankan slot main tanpa antre.
                </p>

                <div class="d-flex flex-wrap gap-3 mb-5">
                    <a href="{{ route('booking.index') }}"
                       class="btn btn-lg rounded-pill px-5 fw-semibold shadow"
                       style="background:#0d9488; color:white; border:none;">
                        <i class="bi bi-calendar3 me-2"></i>Cek Jadwal &amp; Booking
                    </a>
                    <a href="#lapangan"
                       class="btn btn-lg rounded-pill px-4 fw-semibold"
                       style="background:rgba(255,255,255,0.15); color:white; border:1px solid rgba(255,255,255,0.3); backdrop-filter:blur(8px);">
                        Lihat Lapangan
                    </a>
                </div>

                {{-- Stats --}}
                <div class="d-flex gap-5 pt-4" style="border-top:1px solid rgba(255,255,255,0.2);">
                    <div>
                        <div class="fw-bold fs-3">5</div>
                        <small class="opacity-60">Lapangan Vinyl</small>
                    </div>
                    <div>
                        <div class="fw-bold fs-3">07:00</div>
                        <small class="opacity-60">Buka Setiap Hari</small>
                    </div>
                    <div>
                        <div class="fw-bold fs-3" style="color:#fbbf24;">35rb</div>
                        <small class="opacity-60">Mulai Per Jam</small>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Card Info --}}
            <div class="col-lg-6 d-none d-lg-block">
                <div class="ms-auto" style="max-width:420px;">

                    {{-- Glass Card --}}
                    <div class="rounded-4 p-4 mb-3"
                         style="background:rgba(255,255,255,0.12); backdrop-filter:blur(16px); border:1px solid rgba(255,255,255,0.2);">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-3 p-2 d-flex align-items-center justify-content-center"
                                 style="background:#0d9488; width:44px; height:44px; font-size:1.3rem;">
                                🏸
                            </div>
                            <div>
                                <div class="fw-bold text-white">Booking Real-Time</div>
                                <small class="text-white opacity-60">Langsung konfirmasi otomatis</small>
                            </div>
                        </div>
                        <div class="row g-2 text-center text-white">
                            @foreach(['Lap. 1','Lap. 2','Lap. 3','Lap. 4','Lap. 5'] as $i => $l)
                            <div class="col">
                                <div class="rounded-2 py-2 px-1 small fw-semibold"
                                     style="background:{{ $i < 3 ? 'rgba(16,185,129,0.4)' : 'rgba(239,68,68,0.35)' }}; border:1px solid rgba(255,255,255,0.1);">
                                    {{ $l }}<br>
                                    <small style="font-size:.65rem; opacity:.8;">{{ $i < 3 ? 'Tersedia' : 'Penuh' }}</small>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Small info cards --}}
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="rounded-3 p-3 d-flex align-items-center gap-2"
                                 style="background:rgba(255,255,255,0.12); backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,0.15);">
                                <i class="bi bi-shield-check text-success fs-5"></i>
                                <div class="text-white">
                                    <div class="fw-semibold small">Pembayaran Aman</div>
                                    <small class="opacity-60" style="font-size:.7rem;">Transfer + Bukti</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="rounded-3 p-3 d-flex align-items-center gap-2"
                                 style="background:rgba(255,255,255,0.12); backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,0.15);">
                                <i class="bi bi-clock text-warning fs-5"></i>
                                <div class="text-white">
                                    <div class="fw-semibold small">07:00 – 23:00</div>
                                    <small class="opacity-60" style="font-size:.7rem;">Senin – Minggu</small>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    {{-- Wave divider --}}
    <div style="position:absolute; bottom:0; left:0; right:0; z-index:2; line-height:0;">
        <svg viewBox="0 0 1440 60" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <path d="M0,30 C360,70 1080,-10 1440,30 L1440,60 L0,60 Z" fill="#f8fafc"/>
        </svg>
    </div>
</div>


{{-- ═══════════════════════════════════════════
     FASILITAS UNGGULAN
═══════════════════════════════════════════ --}}
<div class="bg-light py-5 border-bottom">
    <div class="container">
        <div class="text-center mb-5">
            <span class="text-primary fw-semibold small text-uppercase tracking-wide">Fasilitas Lengkap</span>
            <h2 class="fw-bold text-dark mt-1">Mengapa Bermain di GOR Metro Badminton?</h2>
        </div>
        <div class="row g-4 text-center">
            @php
            $fitur = [
                ['bi-grid-3x3',    'primary', '5 Lapangan Karpet', 'Karpet vinyl empuk standar PBSI mengurangi risiko cedera lutut & pergelangan.'],
                ['bi-clock-history','success', 'Booking Real-Time', 'Sistem otomatis mendeteksi bentrok jadwal, transparan, tidak ada ganda booking.'],
                ['bi-p-square',    'warning', 'Parkir Luas & Aman', 'Area parkir mobil & motor luas dilengkapi petugas keamanan dan pantauan CCTV.'],
                ['bi-cup-straw',   'info',    'Kantin & Toilet',   'Tersedia minuman dingin, shuttlecock, toilet bersih, dan musholla untuk ibadah.'],
            ];
            @endphp
            @foreach($fitur as [$icon, $warna, $judul, $desc])
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 p-4 text-center">
                    <div class="mb-3">
                        <span class="rounded-circle d-inline-flex align-items-center justify-content-center"
                              style="width:60px;height:60px; background:var(--bs-{{ $warna }}-bg-subtle, #f0fdf4);">
                            <i class="bi {{ $icon }} fs-4 text-{{ $warna }}"></i>
                        </span>
                    </div>
                    <h5 class="fw-bold">{{ $judul }}</h5>
                    <p class="small text-muted mb-0">{{ $desc }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════
     DAFTAR LAPANGAN
═══════════════════════════════════════════ --}}
<div class="container py-5" id="lapangan">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <span class="text-primary fw-semibold small text-uppercase">Daftar Lapangan</span>
            <h2 class="fw-bold text-dark mb-1">Pilih Lapangan Favoritmu</h2>
            <p class="text-muted small mb-0">Tersedia 5 lapangan berkualitas dengan fasilitas prima.</p>
        </div>
        <a href="{{ route('booking.index') }}" class="btn btn-outline-primary rounded-pill d-none d-md-inline-flex align-items-center gap-1">
            Cek Jadwal Semua <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    @php
    $lapanganImages = [
        1 => asset('images/lapangan1.jpg'),
        2 => asset('images/lapangan2.jpg'),
        3 => asset('images/lapangan3.jpg'),
        4 => asset('images/lapangan1.jpg'),
        5 => asset('images/lapangan2.jpg'),
    ];
    @endphp
    <div class="row g-4">
        @foreach($lapangans as $lap)
        @php $imgUrl = $lapanganImages[$lap->nomor_lapangan] ?? $lapanganImages[1]; @endphp
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 overflow-hidden" style="border-radius:16px;">
                <div class="position-relative">
                    <img src="{{ $imgUrl }}"
                         alt="{{ $lap->nama_lapangan }}" class="card-img-top object-fit-cover" style="height:200px;">
                    {{-- Gradient overlay pada gambar --}}
                    <div style="position:absolute;inset:0;background:linear-gradient(to top, rgba(0,0,0,0.5), transparent 50%);"></div>
                    <span class="position-absolute top-0 end-0 m-3 badge {{ $lap->status === 'tersedia' ? 'bg-success' : 'bg-danger' }}">
                        {{ $lap->status === 'tersedia' ? 'Tersedia' : 'Perbaikan' }}
                    </span>
                    <span class="position-absolute bottom-0 start-0 m-3 text-white fw-bold fs-5">
                        Lap. {{ $lap->nomor_lapangan }}
                    </span>
                </div>
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="fw-bold text-dark mb-0 me-2" style="font-size:.95rem;">{{ $lap->nama_lapangan }}</h5>
                        <span class="text-primary fw-bold fs-5 text-nowrap">
                            Rp {{ number_format($lap->harga_per_jam, 0, ',', '.') }}
                            <small class="text-muted fs-6 fw-normal">/jam</small>
                        </span>
                    </div>
                    <p class="small text-muted mb-3 flex-grow-1">{{ $lap->deskripsi }}</p>
                    <div class="pt-3 border-top">
                        @if($lap->status === 'tersedia')
                            <a href="{{ route('booking.create', $lap->id) }}"
                               class="btn w-100 rounded-pill text-white fw-semibold"
                               style="background:linear-gradient(135deg,#0f766e,#0d9488);">
                                <i class="bi bi-plus-circle me-1"></i> Pesan Lapangan Ini
                            </a>
                        @else
                            <button class="btn btn-secondary w-100 rounded-pill" disabled>
                                <i class="bi bi-tools me-1"></i> Sedang Perbaikan
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>


{{-- ═══════════════════════════════════════════
     CTA BANNER — Background Lapangan
═══════════════════════════════════════════ --}}
<div class="container pb-5">
    <div class="position-relative overflow-hidden text-white text-center rounded-4 shadow-lg"
         style="min-height:260px;">
        {{-- Background --}}
        <div style="position:absolute; inset:0;
             background: url('https://images.unsplash.com/photo-1612872087720-bb876e2e67d1?auto=format&fit=crop&w=1200&q=80') center / cover no-repeat;
             filter:brightness(.4);">
        </div>
        <div style="position:absolute; inset:0; background:linear-gradient(135deg, rgba(15,118,110,.7), rgba(15,23,42,.7));"></div>
        {{-- Content --}}
        <div class="position-relative py-5 px-3" style="z-index:2;">
            <span style="font-size:3rem;">🏸</span>
            <h2 class="display-6 fw-bold mb-3 mt-2">Siap Bertanding Hari Ini?</h2>
            <p class="lead text-light mb-4 mx-auto" style="max-width:580px;">
                Jangan sampai kehabisan slot di jam favoritmu. Amankan jadwal main sekarang juga secara online!
            </p>
            <a href="{{ route('booking.index') }}"
               class="btn btn-warning btn-lg rounded-pill px-5 fw-bold text-dark shadow">
                Booking Sekarang <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</div>

@endsection
