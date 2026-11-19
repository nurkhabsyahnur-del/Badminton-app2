<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

// Halaman Publik
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/jadwal', [BookingController::class, 'index'])->name('booking.index');

// Autentikasi
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Fitur Pelanggan (Perlu Login)
Route::middleware('auth')->group(function () {
    Route::get('/booking/lapangan/{lapangan_id}', [BookingController::class, 'create'])->name('booking.create');
    Route::post('/booking/store', [BookingController::class, 'store'])->name('booking.store');
    Route::get('/booking/detail/{kode_booking}', [BookingController::class, 'detail'])->name('booking.detail');
    Route::post('/booking/upload/{kode_booking}', [BookingController::class, 'uploadBukti'])->name('booking.upload');
    Route::post('/booking/batal/{kode_booking}', [BookingController::class, 'batal'])->name('booking.batal');
    Route::get('/riwayat-booking', [BookingController::class, 'riwayat'])->name('booking.riwayat');
    Route::get('/booking/cetak/{kode_booking}', [BookingController::class, 'cetak'])->name('booking.cetak');
});

// Fitur Admin (Perlu Login & Role Admin)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/pesanan', [AdminController::class, 'pesanan'])->name('pesanan');
    Route::post('/pesanan/konfirmasi/{id}', [AdminController::class, 'konfirmasiPesanan'])->name('pesanan.konfirmasi');
    Route::post('/pesanan/batalkan/{id}', [AdminController::class, 'batalkanPesanan'])->name('pesanan.batalkan');
    Route::get('/lapangan', [AdminController::class, 'lapangan'])->name('lapangan');
    Route::post('/lapangan/update/{id}', [AdminController::class, 'updateLapangan'])->name('lapangan.update');
    Route::get('/rekap', [AdminController::class, 'rekap'])->name('rekap');

    // Manajemen Data Anggota (CRUD)
    Route::get('/anggota', [AdminController::class, 'anggota'])->name('anggota.index');
    Route::post('/anggota/store', [AdminController::class, 'storeAnggota'])->name('anggota.store');
    Route::post('/anggota/update/{id}', [AdminController::class, 'updateAnggota'])->name('anggota.update');
    Route::delete('/anggota/{id}', [AdminController::class, 'deleteAnggota'])->name('anggota.delete');
    Route::post('/anggota/delete/{id}', [AdminController::class, 'deleteAnggota']);
});
