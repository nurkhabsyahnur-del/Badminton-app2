<?php

namespace App\Http\Controllers;

use App\Models\Lapangan;
use App\Models\Pemesanan;
use App\Models\Pembayaran;
use App\Models\User;
use App\Services\FonnteService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Auto-cancel pemesanan yang expired (> 5 menit belum bayar)
        Pemesanan::where('status', 'menunggu_pembayaran')
            ->where('batas_pembayaran', '<', Carbon::now())
            ->update(['status' => 'dibatalkan']);

        $today = Carbon::today()->format('Y-m-d');
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;

        // Metrik Sesuai Skripsi Kating (Dashboard Admin)
        $pesananHariIni = Pemesanan::whereDate('tanggal_main', $today)->count();
        $pesananBulanIni = Pemesanan::whereMonth('tanggal_main', $thisMonth)->whereYear('tanggal_main', $thisYear)->count();

        $pendapatanHariIni = Pemesanan::whereDate('tanggal_main', $today)
            ->where('status', 'terkonfirmasi')
            ->sum('total_harga');

        $pendapatanBulanIni = Pemesanan::whereMonth('tanggal_main', $thisMonth)
            ->whereYear('tanggal_main', $thisYear)
            ->where('status', 'terkonfirmasi')
            ->sum('total_harga');

        $menungguKonfirmasi = Pemesanan::where('status', 'menunggu_konfirmasi')->count();

        $totalAnggota = User::where('role', 'pelanggan')->count();
        $anggotaTerbaru = User::where('role', 'pelanggan')->orderBy('created_at', 'desc')->take(5)->get();

        $jadwalHariIni = Pemesanan::with(['user', 'lapangan'])
            ->whereDate('tanggal_main', $today)
            ->whereIn('status', ['menunggu_konfirmasi', 'terkonfirmasi'])
            ->orderBy('jam_mulai', 'asc')
            ->get();

        return view('admin.dashboard', compact(
            'pesananHariIni',
            'pesananBulanIni',
            'pendapatanHariIni',
            'pendapatanBulanIni',
            'menungguKonfirmasi',
            'totalAnggota',
            'anggotaTerbaru',
            'jadwalHariIni'
        ));
    }

    public function pesanan(Request $request)
    {
        // Auto-cancel pemesanan yang expired (> 5 menit belum bayar)
        Pemesanan::where('status', 'menunggu_pembayaran')
            ->where('batas_pembayaran', '<', Carbon::now())
            ->update(['status' => 'dibatalkan']);

        $status = $request->get('status');

        $query = Pemesanan::with(['user', 'lapangan', 'pembayaran'])
            ->orderBy('created_at', 'desc');

        if ($status && in_array($status, ['menunggu_pembayaran', 'menunggu_konfirmasi', 'terkonfirmasi', 'dibatalkan'])) {
            $query->where('status', $status);
        }

        $pesanans = $query->paginate(15);

        return view('admin.pesanan', compact('pesanans', 'status'));
    }

    public function konfirmasiPesanan($id)
    {
        $pemesanan = Pemesanan::with(['pembayaran', 'user', 'lapangan'])->findOrFail($id);
        $pemesanan->update(['status' => 'terkonfirmasi']);

        if ($pemesanan->pembayaran) {
            $pemesanan->pembayaran->update([
                'status_verifikasi' => 'valid',
                'catatan_admin' => 'Pembayaran terverifikasi valid oleh Admin GOR Metro.',
            ]);
        }

        // Kirim notifikasi WhatsApp ke pelanggan via Fonnte
        // Dibungkus try-catch agar jika gagal, konfirmasi tetap sukses
        try {
            if ($pemesanan->user && !empty($pemesanan->user->no_hp)) {
                $tanggal   = Carbon::parse($pemesanan->tanggal_main)->translatedFormat('l, d F Y');
                $jamMulai  = substr($pemesanan->jam_mulai, 0, 5);
                $jamSelesai = substr($pemesanan->jam_selesai, 0, 5);
                $lapangan  = $pemesanan->lapangan ? 'Lapangan ' . $pemesanan->lapangan->nomor_lapangan : '-';
                $total     = 'Rp ' . number_format($pemesanan->total_harga, 0, ',', '.');

                $pesan = "✅ *Konfirmasi Pemesanan Lapangan Badminton*\n\n"
                    . "Halo, *{$pemesanan->user->name}*!\n"
                    . "Pembayaran Anda telah *dikonfirmasi* oleh Admin GOR Metro Badminton. 🏸\n\n"
                    . "📋 *Detail Pemesanan:*\n"
                    . "🔖 Kode Booking : {$pemesanan->kode_booking}\n"
                    . "🏟️ Lapangan     : {$lapangan}\n"
                    . "📅 Tanggal      : {$tanggal}\n"
                    . "⏰ Jam Bermain  : {$jamMulai} - {$jamSelesai} WIB\n"
                    . "⏳ Durasi       : {$pemesanan->durasi_jam} Jam\n"
                    . "💰 Total Bayar  : {$total}\n\n"
                    . "Silakan datang tepat waktu. Terima kasih telah memilih GOR Metro Badminton! 🙏";

                $fonnte = new FonnteService();
                $fonnte->kirim($pemesanan->user->no_hp, $pesan);
            }
        } catch (\Throwable $e) {
            // Tidak pernah error 500 meski WA gagal terkirim
            \Illuminate\Support\Facades\Log::warning('[Konfirmasi] Gagal kirim notifikasi WA: ' . $e->getMessage());
        }

        return back()->with('success', 'Pesanan ' . $pemesanan->kode_booking . ' berhasil dikonfirmasi! Notifikasi WhatsApp telah dikirim ke pelanggan.');
    }

    public function batalkanPesanan(Request $request, $id)
    {
        $pemesanan = Pemesanan::with('pembayaran')->findOrFail($id);
        $alasan = $request->input('catatan_admin', 'Dibatalkan oleh Pengurus GOR.');

        $pemesanan->update([
            'status' => 'dibatalkan',
            'catatan' => $alasan
        ]);

        if ($pemesanan->pembayaran) {
            $pemesanan->pembayaran->update([
                'status_verifikasi' => 'tidak_valid',
                'catatan_admin' => $alasan
            ]);
        }

        return back()->with('success', 'Pesanan ' . $pemesanan->kode_booking . ' telah dibatalkan.');
    }

    public function lapangan()
    {
        $lapangans = Lapangan::orderBy('nomor_lapangan', 'asc')->get();
        return view('admin.lapangan', compact('lapangans'));
    }

    public function updateLapangan(Request $request, $id)
    {
        $lapangan = Lapangan::findOrFail($id);

        $request->validate([
            'nama_lapangan' => 'required|string|max:100',
            'harga_per_jam' => 'required|numeric|min:0',
            'status' => 'required|in:tersedia,perbaikan',
            'deskripsi' => 'nullable|string',
        ]);

        $lapangan->update($request->only(['nama_lapangan', 'harga_per_jam', 'status', 'deskripsi']));

        return back()->with('success', 'Data Lapangan ' . $lapangan->nomor_lapangan . ' berhasil diperbarui.');
    }

    public function rekap(Request $request)
    {
        $filterType = $request->get('filter_type', 'bulanan');
        $bulan = $request->get('bulan', Carbon::now()->format('m'));
        $tahun = $request->get('tahun', Carbon::now()->format('Y'));
        $tanggal = $request->get('tanggal', Carbon::today()->format('Y-m-d'));

        $query = Pemesanan::with(['user', 'lapangan', 'pembayaran'])
            ->where('status', 'terkonfirmasi');

        if ($filterType === 'harian') {
            $query->whereDate('tanggal_main', $tanggal);
        } else {
            $query->whereMonth('tanggal_main', $bulan)
                  ->whereYear('tanggal_main', $tahun);
        }

        $transaksi = $query->orderBy('tanggal_main', 'asc')
            ->orderBy('jam_mulai', 'asc')
            ->get();

        $totalPendapatan = $transaksi->sum('total_harga');
        $totalDurasi = $transaksi->sum('durasi_jam');
        $totalPenyewaan = $transaksi->count();

        if ($request->has('print')) {
            return view('admin.rekap_cetak', compact('transaksi', 'totalPendapatan', 'totalDurasi', 'totalPenyewaan', 'filterType', 'bulan', 'tahun', 'tanggal'));
        }

        return view('admin.rekap', compact('transaksi', 'totalPendapatan', 'totalDurasi', 'totalPenyewaan', 'filterType', 'bulan', 'tahun', 'tanggal'));
    }

    public function anggota(Request $request)
    {
        $keyword = $request->input('q');
        $selectedRole = $request->input('role');

        $query = User::withCount('pemesanan')->orderBy('created_at', 'desc');

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('username', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%")
                  ->orWhere('no_hp', 'like', "%{$keyword}%");
            });
        }

        if (!empty($selectedRole) && in_array($selectedRole, ['pelanggan', 'admin'])) {
            $query->where('role', $selectedRole);
        }

        $users = $query->paginate(10)->withQueryString();

        $totalUsers = User::count();
        $totalPelanggan = User::where('role', 'pelanggan')->count();
        $totalAdmin = User::where('role', 'admin')->count();

        return view('admin.anggota', compact(
            'users',
            'totalUsers',
            'totalPelanggan',
            'totalAdmin',
            'keyword',
            'selectedRole'
        ));
    }

    public function storeAnggota(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'username' => 'required|string|max:50|alpha_dash|unique:users,username',
            'email' => 'required|email|max:150|unique:users,email',
            'no_hp' => 'required|numeric|digits_between:10,15',
            'password' => 'required|string|min:6',
            'role' => 'required|in:pelanggan,admin',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan oleh akun lain.',
            'username.alpha_dash' => 'Username hanya boleh huruf, angka, tanda hubung, dan garis bawah.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'no_hp.required' => 'Nomor HP/WhatsApp wajib diisi.',
            'no_hp.numeric' => 'Nomor HP harus berupa angka.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal harus 6 karakter.',
            'role.required' => 'Peran (role) wajib dipilih.',
        ]);

        User::create([
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'email' => strtolower($validated['email']),
            'no_hp' => $validated['no_hp'],
            'password' => bcrypt($validated['password']),
            'role' => $validated['role'],
            'foto' => 'default_user.png',
        ]);

        return redirect()->route('admin.anggota.index')->with('success', 'Anggota baru (' . $validated['name'] . ') berhasil ditambahkan.');
    }

    public function updateAnggota(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'no_hp' => 'required|numeric|digits_between:10,15',
            'role' => 'required|in:pelanggan,admin',
            'password' => 'nullable|string|min:6',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan oleh akun lain.',
            'username.alpha_dash' => 'Username hanya boleh huruf, angka, tanda hubung, dan garis bawah.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'no_hp.required' => 'Nomor HP/WhatsApp wajib diisi.',
            'no_hp.numeric' => 'Nomor HP harus berupa angka.',
            'password.min' => 'Password minimal harus 6 karakter.',
            'role.required' => 'Peran (role) wajib dipilih.',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'email' => strtolower($validated['email']),
            'no_hp' => $validated['no_hp'],
            'role' => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = bcrypt($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('admin.anggota.index')->with('success', 'Data anggota (' . $user->name . ') berhasil diperbarui.');
    }

    public function deleteAnggota($id)
    {
        $user = User::findOrFail($id);

        // Cegah admin menghapus akunnya sendiri yang sedang aktif login
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif login.');
        }

        $nama = $user->name;
        $user->delete();

        return redirect()->route('admin.anggota.index')->with('success', 'Akun anggota ' . $nama . ' berhasil dihapus dari sistem.');
    }
}
