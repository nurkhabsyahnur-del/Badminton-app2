<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Akun – GOR Metro Badminton</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        * { font-family: 'Poppins', sans-serif; }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
        }

        /* ── Kiri: Background Lapangan Badminton ── */
        .login-bg {
            flex: 1;
            position: relative;
            background: url('https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=1200&q=85') center center / cover no-repeat;
            display: flex;
            align-items: flex-end;
            padding: 3rem;
        }
        .login-bg::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg,
                rgba(15, 118, 110, 0.80) 0%,
                rgba(15, 23, 42, 0.75) 100%);
        }
        .login-bg-content {
            position: relative;
            z-index: 2;
            color: white;
        }
        .shuttlecock-icon {
            font-size: 4rem;
            opacity: 0.9;
            margin-bottom: 1rem;
            display: block;
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50%       { transform: translateY(-10px); }
        }
        .court-lines {
            position: absolute;
            inset: 0;
            opacity: 0.06;
            background-image:
                linear-gradient(white 1px, transparent 1px),
                linear-gradient(90deg, white 1px, transparent 1px);
            background-size: 60px 60px;
        }

        /* ── Kanan: Form ── */
        .login-form-panel {
            width: 460px;
            min-width: 360px;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 2rem;
            overflow-y: auto;
        }
        .login-form-inner { width: 100%; max-width: 380px; }

        .form-control, .input-group-text {
            border-radius: 10px !important;
            border-color: #e2e8f0;
        }
        .input-group .input-group-text { border-radius: 10px 0 0 10px !important; }
        .input-group .form-control      { border-radius: 0 10px 10px 0 !important; }

        .btn-login {
            background: linear-gradient(135deg, #0f766e, #0d9488);
            border: none;
            border-radius: 50px;
            font-weight: 600;
            letter-spacing: .3px;
            transition: all .25s;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #0d9488, #0f766e);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(15,118,110,.35);
        }

        .logo-badge {
            width: 60px; height: 60px;
            background: linear-gradient(135deg, #0f766e, #0d9488);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.8rem; color: white;
            box-shadow: 0 4px 15px rgba(15,118,110,.3);
        }

        /* Responsive */
        @media (max-width: 767px) {
            .login-bg { display: none; }
            .login-form-panel { width: 100%; }
        }
    </style>
</head>
<body>
<div class="login-wrapper">

    {{-- ── KIRI: Panel Gambar Lapangan ── --}}
    <div class="login-bg">
        <div class="court-lines"></div>
        <div class="login-bg-content">
            <span class="shuttlecock-icon">🏸</span>
            <h2 class="fw-bold fs-1 mb-2" style="line-height:1.2;">
                GOR Metro<br>Badminton
            </h2>
            <p class="mb-4 opacity-75" style="max-width:340px;line-height:1.7;">
                Sistem penyewaan lapangan badminton online — cek jadwal, booking, dan bayar dari mana saja.
            </p>
            <div class="d-flex gap-4">
                <div>
                    <div class="fw-bold fs-4">5</div>
                    <small class="opacity-75">Lapangan Vinyl</small>
                </div>
                <div>
                    <div class="fw-bold fs-4">07.00</div>
                    <small class="opacity-75">Buka Setiap Hari</small>
                </div>
                <div>
                    <div class="fw-bold fs-4" style="color:#fbbf24;">35rb</div>
                    <small class="opacity-75">Per Jam</small>
                </div>
            </div>
            <div class="mt-5 pt-3 border-top border-white border-opacity-25">
                <small class="opacity-50">Jl. Kanguru Hadimulyo Timur, Metro Pusat</small>
            </div>
        </div>
    </div>

    {{-- ── KANAN: Form Login ── --}}
    <div class="login-form-panel">
        <div class="login-form-inner">

            {{-- Logo --}}
            <div class="text-center mb-4">
                <div class="logo-badge">
                    <i class="bi bi-person-lock"></i>
                </div>
                <h4 class="fw-bold text-dark mb-1">Masuk Akun</h4>
                <p class="text-muted small mb-0">Silakan login untuk memesan lapangan.</p>
            </div>

            {{-- Error --}}
            @if($errors->has('login_error'))
                <div class="alert alert-danger py-2 small rounded-3 d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    {{ $errors->first('login_error') }}
                </div>
            @endif

            {{-- Form --}}
            <form action="{{ route('login') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-person text-muted"></i></span>
                        <input type="text" name="username"
                               class="form-control @error('username') is-invalid @enderror"
                               value="{{ old('username') }}"
                               placeholder="Masukkan username" required autofocus>
                    </div>
                    @error('username')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-lock text-muted"></i></span>
                        <input type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="••••••••" required id="passwordInput">
                        <button type="button" class="input-group-text bg-white border-start-0"
                                onclick="togglePass()" style="cursor:pointer; border-radius:0 10px 10px 0 !important;">
                            <i class="bi bi-eye text-muted" id="eyeIcon"></i>
                        </button>
                    </div>
                    @error('password')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-4 form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label small text-secondary" for="remember">Ingat Saya</label>
                </div>

                <button type="submit" class="btn btn-login text-white w-100 py-2 mb-3">
                    Masuk Sekarang &nbsp;<i class="bi bi-arrow-right"></i>
                </button>
            </form>

            <div class="text-center pt-3 border-top">
                <p class="small text-muted mb-3">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="fw-semibold text-decoration-none" style="color:#0f766e;">
                        Daftar di sini
                    </a>
                </p>


            </div>

            <div class="text-center mt-4">
                <a href="{{ route('home') }}" class="small text-muted text-decoration-none">
                    <i class="bi bi-arrow-left me-1"></i>Kembali ke Halaman Utama
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function togglePass() {
    const inp = document.getElementById('passwordInput');
    const ico = document.getElementById('eyeIcon');
    if (inp.type === 'password') {
        inp.type = 'text';
        ico.className = 'bi bi-eye-slash text-muted';
    } else {
        inp.type = 'password';
        ico.className = 'bi bi-eye text-muted';
    }
}
</script>
</body>
</html>
