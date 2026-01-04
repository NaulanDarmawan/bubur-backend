<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Hitung Statistik Real-time dari Database
        $totalUsers = User::where('role', 'user')->count();
        $totalProducts = Product::count();

        // Rental Aktif = Status 'active' (Sedang dibawa) atau 'pending' (Booking baru)
        $totalRentals = Rental::whereIn('status', ['active', 'pending'])->count();

        // User yang butuh verifikasi KTP
        $pendingKyc = User::where('kyc_status', 'pending')->count();

        // 2. Kirim data ke View 'dashboard' (bawaan Breeze yang sudah kita edit)
        return view('dashboard', compact('totalUsers', 'totalProducts', 'totalRentals', 'pendingKyc'));
    }
}
