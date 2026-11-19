<?php

namespace App\Http\Controllers;

use App\Models\Lapangan;
use App\Models\Pemesanan;
use App\Models\Pembayaran;
use App\Models\RekeningPembayaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Auto-cancel pemesanan yang lewat batas 5 menit agar slot kembali kosong
            Pemesanan::where('status', 'menunggu_pembayaran')
                ->where('batas_pembayaran', '<', Carbon::now())
                ->update(['status' => 'dibatalkan']);

            $lapangans = Lapangan::where('status', 'tersedia')->orderBy('nomor_lapangan', 'asc')->get();
        } catch (\Throwable $e) {
            $lapangans = collect();
        }

        $selectedDate = $request->get('tanggal', Carbon::today()->format('Y-m-d'));
        $selectedLapanganId = $request->get('lapangan_id', $lapangans->first()?->id);

        try {
            $bookings = $selectedLapanganId ? Pemesanan::with('user')
                ->where('lapangan_id', $selectedLapanganId)
                ->where('tanggal_main', $selectedDate)
                ->whereIn('status', ['menunggu_pembayaran', 'menunggu_konfirmasi', 'terkonfirmasi'])
                ->orderBy('jam_mulai', 'asc')
                ->get() : collect();
        } catch (\Throwable $e) {
            $bookings = collect();
        }

        return view('booking.index', compact('lapangans', 'selectedDate', 'selectedLapanganId', 'bookings'));
    }

    public function create($lapangan_id)
    {
        $lapangan = Lapangan::findOrFail($lapangan_id);
        if ($lapangan->status !== 'tersedia') {
            return redirect()->route('booking.index')->with('error', 'Lapangan ini sedang dalam masa perbaikan.');
        }

        return view('booking.create', compact('lapangan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'lapangan_id' => 'required|exists:lapangan,id',
            'tanggal_main' => 'required|date|after_or_equal:today',
            'jam_mulai' => 'required|date_format:H:i',
            'durasi_jam' => 'required|integer|min:1|max:6',
            'catatan' => 'nullable|string|max:255',
        ], [
            'tanggal_main.after_or_equal' => 'Tanggal sewa tidak boleh sebelum hari ini.',
            'jam_mulai.required' => 'Pilih jam mulai sewa.',
            'durasi_jam.required' => 'Tentukan durasi sewa.',
            'durasi_jam.min' => 'Durasi minimal 1 jam.',
            'durasi_jam.max' => 'Durasi maksimal 6 jam sekali booking.',
        ]);

        $lapangan = Lapangan::findOrFail($request->lapangan_id);
        
        $jamMulai = Carbon::createFromFormat('H:i', $request->jam_mulai);
        $jamSelesai = (clone $jamMulai)->addHours((int) $request->durasi_jam);

        $jamMulaiStr = $jamMulai->format('H:i:s');
        $jamSelesaiStr = $jamSelesai->format('H:i:s');

        // Jam operasional GOR Metro Badminton: 07:00 s/d 23:00
        if ($jamMulai->hour < 7 || $jamSelesai->hour > 23 || ($jamSelesai->hour == 23 && $jamSelesai->minute > 0)) {
            return back()->withInput()->with('error', 'Jam operasional GOR Metro Badminton adalah pukul 07:00 hingga 23:00.');
        }

        // Cek Bentrok Jadwal
        $bentrok = Pemesanan::where('lapangan_id', $lapangan->id)
            ->where('tanggal_main', $request->tanggal_main)
            ->whereIn('status', ['menunggu_pembayaran', 'menunggu_konfirmasi', 'terkonfirmasi'])
            ->where(function ($query) use ($jamMulaiStr, $jamSelesaiStr) {
                $query->where(function ($q) use ($jamMulaiStr, $jamSelesaiStr) {
                    $q->where('jam_mulai', '<', $jamSelesaiStr)
                      ->where('jam_selesai', '>', $jamMulaiStr);
                });
            })
            ->first();

        if ($bentrok) {
            return back()->withInput()->with('error', 'Maaf, jadwal pada tanggal ' . Carbon::parse($request->tanggal_main)->format('d M Y') . ' pukul ' . substr($bentrok->jam_mulai, 0, 5) . ' - ' . substr($bentrok->jam_selesai, 0, 5) . ' sudah dipesan oleh orang lain. Silakan pilih jam atau lapangan lain.');
        }

        $totalHarga = $lapangan->harga_per_jam * (int) $request->durasi_jam;
        $kodeBooking = 'MB-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        $batasPembayaran = Carbon::now()->addMinutes(5);

        $pemesanan = Pemesanan::create([
            'kode_booking' => $kodeBooking,
            'user_id' => Auth::id(),
            'lapangan_id' => $lapangan->id,
            'tanggal_main' => $request->tanggal_main,
            'jam_mulai' => $jamMulaiStr,
            'durasi_jam' => $request->durasi_jam,
            'jam_selesai' => $jamSelesaiStr,
            'total_harga' => $totalHarga,
            'status' => 'menunggu_pembayaran',
            'batas_pembayaran' => $batasPembayaran,
            'catatan' => $request->catatan,
        ]);

        return redirect()->route('booking.detail', $pemesanan->kode_booking)
            ->with('success', 'Pemesanan berhasil dibuat! Silakan selesaikan pembayaran sebelum batas waktu berakhir.');
    }

    public function detail($kode_booking)
    {
        $pemesanan = Pemesanan::with(['lapangan', 'pembayaran.rekening'])
            ->where('kode_booking', $kode_booking)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Auto-Cancel jika melewati batas pembayaran 5 menit
        if ($pemesanan->status === 'menunggu_pembayaran' && Carbon::now()->gt($pemesanan->batas_pembayaran)) {
            $pemesanan->update(['status' => 'dibatalkan']);
            $pemesanan->refresh();
        }

        $rekeningList = RekeningPembayaran::where('is_active', true)->get();

        return view('booking.detail', compact('pemesanan', 'rekeningList'));
    }

    public function uploadBukti(Request $request, $kode_booking)
    {
        $pemesanan = Pemesanan::where('kode_booking', $kode_booking)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($pemesanan->status === 'menunggu_pembayaran' && Carbon::now()->gt($pemesanan->batas_pembayaran)) {
            $pemesanan->update(['status' => 'dibatalkan']);
            return back()->with('error', 'Pesanan ini sudah dibatalkan otomatis karena melewati batas waktu 5 menit.');
        }

        if ($pemesanan->status === 'dibatalkan') {
            return back()->with('error', 'Pesanan ini sudah dibatalkan karena melewati batas waktu pembayaran.');
        }

        $request->validate([
            'metode_pembayaran' => 'required|in:transfer_bank,qris',
            'rekening_id' => 'required_if:metode_pembayaran,transfer_bank|nullable|exists:rekening_pembayaran,id',
            'nama_pengirim' => 'required|string|max:150',
            'bank_pengirim' => 'nullable|string|max:100',
            'bukti_transfer' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'rekening_id.required_if' => 'Pilih rekening bank tujuan.',
            'nama_pengirim.required' => 'Nama pengirim / akun wajib diisi.',
            'bukti_transfer.required' => 'File bukti transfer atau screenshot QRIS wajib diunggah.',
            'bukti_transfer.image' => 'File harus berupa foto/gambar/screenshot.',
            'bukti_transfer.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        $file = $request->file('bukti_transfer');
        $mime = $file->getMimeType() ?: 'image/'.$file->getClientOriginalExtension();
        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
        ];
        $extension = $allowedMimes[$mime] ?? null;

        if ($extension === null) {
            return back()->with('error', 'Format gambar tidak didukung. Gunakan JPG atau PNG.');
        }

        // Di Vercel, storage/ tidak persisten: simpan gambar sebagai data URI di database.
        $uploadDriver = env('UPLOAD_DRIVER', env('VERCEL') ? 'database' : 'file');

        if ($uploadDriver === 'database') {
            $buktiTransfer = 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($file->getRealPath()));
        } else {
            $buktiTransfer = 'bukti_'.time().'_'.Str::random(6).'.'.$extension;
            Storage::disk('public')->putFileAs('bukti_transfer', $file, $buktiTransfer);
        }

        Pembayaran::updateOrCreate(
            ['pemesanan_id' => $pemesanan->id],
            [
                'rekening_id' => $request->metode_pembayaran === 'transfer_bank' ? $request->rekening_id : null,
                'metode_pembayaran' => $request->metode_pembayaran,
                'nama_pengirim' => $request->nama_pengirim,
                'bank_pengirim' => $request->bank_pengirim ?: ($request->metode_pembayaran === 'qris' ? 'QRIS / E-Wallet' : '-'),
                'jumlah_dibayar' => $pemesanan->total_harga,
                'bukti_transfer' => $buktiTransfer,
                'status_verifikasi' => 'menunggu',
                'tanggal_bayar' => Carbon::now(),
            ]
        );

        $pemesanan->update(['status' => 'menunggu_konfirmasi']);

        return back()->with('success', 'Bukti pembayaran berhasil diunggah! Pengurus akan segera memverifikasi pesanan Anda.');
    }

    public function riwayat()
    {
        // Auto-cancel pemesanan yang lewat batas 5 menit
        Pemesanan::where('user_id', Auth::id())
            ->where('status', 'menunggu_pembayaran')
            ->where('batas_pembayaran', '<', Carbon::now())
            ->update(['status' => 'dibatalkan']);

        $pemesanans = Pemesanan::with(['lapangan', 'pembayaran'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('booking.riwayat', compact('pemesanans'));
    }

    public function batal($kode_booking)
    {
        $pemesanan = Pemesanan::where('kode_booking', $kode_booking)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($pemesanan->status !== 'menunggu_pembayaran') {
            return back()->with('error', 'Hanya pesanan yang belum dibayar yang dapat dibatalkan.');
        }

        $pemesanan->update(['status' => 'dibatalkan']);

        return back()->with('success', 'Pesanan Anda telah berhasil dibatalkan.');
    }

    public function cetak($kode_booking)
    {
        $pemesanan = Pemesanan::with(['lapangan', 'user', 'pembayaran.rekening'])
            ->where('kode_booking', $kode_booking)
            ->firstOrFail();

        // Hanya pemilik atau admin yang bisa cetak
        if (Auth::user()->role !== 'admin' && $pemesanan->user_id !== Auth::id()) {
            abort(403);
        }

        return view('booking.invoice', compact('pemesanan'));
    }
}
