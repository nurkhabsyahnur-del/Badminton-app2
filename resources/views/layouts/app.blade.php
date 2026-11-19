<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistem Penyewaan Lapangan') - GOR Metro Badminton</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar-brand {
            font-weight: 700;
            color: #0f766e !important;
            letter-spacing: -0.5px;
        }
        .btn-primary {
            background-color: #0f766e;
            border-color: #0f766e;
        }
        .btn-primary:hover {
            background-color: #115e59;
            border-color: #115e59;
        }
        .btn-outline-primary {
            color: #0f766e;
            border-color: #0f766e;
        }
        .btn-outline-primary:hover {
            background-color: #0f766e;
            color: #fff;
        }
        .badge-status {
            font-size: 0.85rem;
            padding: 0.4em 0.7em;
            border-radius: 6px;
        }
        .btn-floating-wa {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #25D366;
            color: white;
            border-radius: 50px;
            padding: 10px 18px;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1050;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(37, 211, 102, 0.4);
        }
        .btn-floating-wa:hover {
            background: #1eb956;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(37, 211, 102, 0.55);
        }
        .footer {
            margin-top: auto;
            background-color: #0f172a;
            color: #94a3b8;
        }
        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card-custom:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
                <span class="p-2 bg-emerald rounded-3 text-white" style="background:#0f766e;">
                    <i class="bi bi-trophy-fill"></i>
                </span>
                <span>GOR Metro Badminton</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active fw-semibold text-primary' : '' }}" href="{{ route('home') }}">
                            <i class="bi bi-house me-1"></i> Beranda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('booking.index') ? 'active fw-semibold text-primary' : '' }}" href="{{ route('booking.index') }}">
                            <i class="bi bi-calendar3 me-1"></i> Cek Jadwal & Lapangan
                        </a>
                    </li>
                    @auth
                        @if(Auth::user()->role === 'pelanggan')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('booking.riwayat') ? 'active fw-semibold text-primary' : '' }}" href="{{ route('booking.riwayat') }}">
                                    <i class="bi bi-receipt me-1"></i> Riwayat Sewa
                                </a>
                            </li>
                        @endif
                        @if(Auth::user()->role === 'admin')
                            <li class="nav-item">
                                <a class="nav-link text-danger fw-bold" href="{{ route('admin.dashboard') }}">
                                    <i class="bi bi-speedometer2 me-1"></i> Dashboard Admin
                                </a>
                            </li>
                        @endif
                    @endauth
                </ul>

                <div class="d-flex align-items-center gap-2">
                    @guest
                        <a href="{{ route('login') }}" class="btn btn-outline-primary px-4 rounded-pill">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-primary px-4 rounded-pill">
                            Daftar Akun
                        </a>
                    @else
                        <div class="dropdown">
                            <button class="btn btn-light rounded-pill border dropdown-toggle px-3 py-2 d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle fs-5 text-primary"></i>
                                <span class="fw-medium">{{ Auth::user()->name }}</span>
                                <span class="badge bg-secondary ms-1">{{ ucfirst(Auth::user()->role) }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                                @if(Auth::user()->role === 'pelanggan')
                                    <li><a class="dropdown-item" href="{{ route('booking.riwayat') }}"><i class="bi bi-receipt me-2"></i>Pesanan Saya</a></li>
                                @else
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Panel Admin</a></li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button class="dropdown-item text-danger" type="submit">
                                            <i class="bi bi-box-arrow-right me-2"></i>Keluar
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Alerts -->
    <div class="container mt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="py-4">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-5">
                    <h5 class="text-white fw-bold d-flex align-items-center gap-2">
                        <i class="bi bi-trophy-fill text-warning"></i> GOR Metro Badminton
                    </h5>
                    <p class="small text-secondary mt-2">
                        Sistem Informasi Reservasi Lapangan Badminton Terpadu. Memudahkan pemain memeriksa ketersediaan jadwal secara real-time dan booking lapangan tanpa antre.
                    </p>
                    <p class="small text-secondary mb-0">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i> Jl. Kanguru, Hadimulyo Timur, Kec. Metro Pusat, Kota Metro, Lampung
                    </p>
                </div>
                <div class="col-lg-3">
                    <h6 class="text-white fw-semibold mb-3">Jam Operasional</h6>
                    <ul class="list-unstyled small text-secondary">
                        <li class="mb-2"><i class="bi bi-clock me-2"></i>Senin - Minggu: 07:00 - 23:00 WIB</li>
                        <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i>5 Lapangan Vinyl PBSI</li>
                        <li><i class="bi bi-lightning-charge text-warning me-2"></i>Pencahayaan LED Standar Turnamen</li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6 class="text-white fw-semibold mb-3">Hubungi Pengurus</h6>
                    <p class="small text-secondary mb-2">Punya pertanyaan seputar sewa lapangan atau turnamen?</p>
                    <a href="https://wa.me/62887437641573" target="_blank" class="btn btn-success btn-sm rounded-pill px-3">
                        <i class="bi bi-whatsapp me-1"></i> Chat WhatsApp Pengurus
                    </a>
                </div>
            </div>
            <hr class="border-secondary my-4">
            <div class="text-center small text-secondary">
                &copy; {{ date('Y') }} GOR Metro Badminton. Dikembangkan untuk Tugas Akhir / Skripsi Ilmu Komputer.
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp Button -->
    <a href="https://wa.me/62887437641573?text=Halo%20Admin%20GOR%20Metro%20Badminton,%20saya%20ingin%20bertanya%20seputar%20sewa%20lapangan."
       target="_blank"
       class="btn-floating-wa"
       title="Hubungi Admin via WhatsApp">
        <i class="bi bi-whatsapp fs-5"></i>
        <span class="d-none d-md-inline ms-2 fw-semibold">Chat Admin</span>
    </a>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
