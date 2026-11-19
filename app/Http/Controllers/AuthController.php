<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        try {
            if (Auth::check()) {
                return Auth::user()->role === 'admin' 
                    ? redirect()->route('admin.dashboard') 
                    : redirect()->route('home');
            }
        } catch (\Throwable $e) {
            // Biarkan lanjut ke form login jika DB belum siap
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        try {
            if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']], $request->filled('remember'))) {
                $request->session()->regenerate();

                if (Auth::user()->role === 'admin') {
                    return redirect()->intended(route('admin.dashboard'))->with('success', 'Selamat datang Admin!');
                }
                return redirect()->intended(route('home'))->with('success', 'Berhasil login! Selamat datang di GOR Metro Badminton.');
            }
        } catch (\Throwable $e) {
            return back()->withErrors([
                'login_error' => 'Koneksi database belum tersedia atau bermasalah. Harap periksa koneksi database Anda.',
            ])->onlyInput('username');
        }

        return back()->withErrors([
            'login_error' => 'Username atau password yang Anda masukkan salah.',
        ])->onlyInput('username');
    }

    public function showRegister()
    {
        try {
            if (Auth::check()) {
                return redirect()->route('home');
            }
        } catch (\Throwable $e) {
            // Biarkan lanjut ke form register jika DB belum siap
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:150',
                'username' => 'required|string|max:50|unique:users,username|alpha_dash',
                'email' => 'required|email|max:150|unique:users,email',
                'no_hp' => 'required|numeric|digits_between:10,15',
                'password' => 'required|string|min:8|confirmed',
            ], [
                'name.required' => 'Nama lengkap wajib diisi.',
                'username.required' => 'Username wajib diisi.',
                'username.unique' => 'Username ini sudah terdaftar, silakan pilih yang lain.',
                'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
                'email.required' => 'Email wajib diisi.',
                'email.unique' => 'Email ini sudah terdaftar.',
                'no_hp.required' => 'Nomor WhatsApp / HP wajib diisi.',
                'no_hp.numeric' => 'Nomor WhatsApp / HP harus berupa angka.',
                'password.required' => 'Password wajib diisi.',
                'password.min' => 'Password minimal harus 8 karakter.',
                'password.confirmed' => 'Konfirmasi password tidak cocok.',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'username' => strtolower($validated['username']),
                'email' => strtolower($validated['email']),
                'no_hp' => $validated['no_hp'],
                'password' => Hash::make($validated['password']),
                'role' => 'pelanggan',
            ]);

            Auth::login($user);

            return redirect()->route('home')->with('success', 'Akun berhasil dibuat! Silakan pilih jadwal lapangan.');
        } catch (\Throwable $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                throw $e;
            }
            return back()->withErrors([
                'register_error' => 'Koneksi database belum tersedia. Harap periksa konfigurasi database Anda.',
            ])->withInput();
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }
}
