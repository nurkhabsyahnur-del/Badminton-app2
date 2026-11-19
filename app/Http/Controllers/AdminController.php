<?php

namespace App\Http\Controllers;

use App\Models\Lapangan;
use App\Models\Pemesanan;
use App\Models\Pembayaran;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
        $pemesanan = Pemesanan::with('pembayaran')->findOrFail($id);
        $pemesanan->update(['status' => 'terkonfirmasi']);

        if ($pemesanan->pembayaran) {
            $pemesanan->pembayaran->update([
                'status_verifikasi' => 'valid',
                'catatan_admin' => 'Pembayaran terverifikasi valid oleh Admin GOR Metro.',
            ]);
        }

        return back()->with('success', 'Pesanan ' . $pemesanan->kode_booking . ' berhasil dikonfirmasi!');
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
}
