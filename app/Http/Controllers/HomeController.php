<?php

namespace App\Http\Controllers;

use App\Models\Lapangan;

class HomeController extends Controller
{
    public function index()
    {
        $lapangans = Lapangan::orderBy('nomor_lapangan', 'asc')->get();
        return view('home', compact('lapangans'));
    }
}
