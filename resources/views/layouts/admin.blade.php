<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - GOR Metro Badminton</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f1f5f9;
        }
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background-color: #0f172a;
            color: #94a3b8;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
        }
        .sidebar .nav-link {
            color: #94a3b8;
            padding: 0.8rem 1.25rem;
            border-radius: 8px;
            margin: 0.2rem 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.95rem;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: #1e293b;
            color: #38bdf8;
            font-weight: 500;
        }
        .main-content {
            margin-left: 260px;
            padding: 2rem;
            min-height: 100vh;
        }
        .stat-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
        @media (max-width: 992px) {
            .sidebar {
                position: relative;
                width: 100%;
                min-height: auto;
            }
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar d-flex flex-column p-3">
        <div class="px-3 py-2 mb-4 d-flex align-items-center gap-2 border-bottom border-secondary pb-3">
            <span class="p-2 bg-primary rounded-3 text-white">
                <i class="bi bi-shield-check"></i>
            </span>
            <div>
                <h6 class="text-white fw-bold mb-0">GOR Metro Admin</h6>
                <small class="text-secondary">Panel Pengurus</small>
            </div>
        </div>

        <ul class="nav flex-column mb-auto">
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.pesanan') }}" class="nav-link {{ request()->routeIs('admin.pesanan') ? 'active' : '' }}">
                    <i class="bi bi-calendar2-check"></i> Kelola Pesanan
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.lapangan') }}" class="nav-link {{ request()->routeIs('admin.lapangan') ? 'active' : '' }}">
                    <i class="bi bi-grid-3x3-gap"></i> Data Lapangan
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.anggota.index') }}" class="nav-link {{ request()->routeIs('admin.anggota*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Data Anggota
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.rekap') }}" class="nav-link {{ request()->routeIs('admin.rekap') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-bar-graph"></i> Rekapitulasi Laporan
                </a>
            </li>
            <li class="nav-item mt-3">
                <a href="{{ route('home') }}" class="nav-link text-warning" target="_blank">
                    <i class="bi bi-box-arrow-up-right"></i> Lihat Web Publik
                </a>
            </li>
        </ul>

        <div class="border-top border-secondary pt-3 mt-auto px-2">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-white small fw-semibold">{{ Auth::user()->name }}</div>
                    <small class="text-muted">Administrator</small>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="btn btn-sm btn-outline-danger" title="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="main-content">
        <!-- Flash Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
