<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller; // ✅ tambahkan baris ini

class HomeController extends Controller
{
    public function index()
    {
        // Ambil paket/layanan yang sedang aktif (status diproses atau selesai)
        $activeOrders = Auth::user()->orders()
            ->whereIn('status', ['diproses', 'selesai'])
            ->latest()
            ->get();

        $latestOrder = Auth::user()->orders()->latest()->first();

        // Tampilkan halaman dashboard setelah user login
        return view('dashboard', compact('activeOrders', 'latestOrder'));
    }
}
