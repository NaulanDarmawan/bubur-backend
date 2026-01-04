<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\Rental;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use App\Services\PaymentService;

class RentalService
{
    public function createRental(array $data, int $userId): Rental
    {
        $user = \App\Models\User::findOrFail($userId);

        // Hanya user 'verified' yang boleh lewat
        if ($user->kyc_status !== 'verified') {
            throw ValidationException::withMessages([
                'kyc_status' => ['Akun Anda belum terverifikasi. Silakan upload KTP dan tunggu persetujuan Admin sebelum menyewa.'],
            ]);
        }
        
        $product = Product::findOrFail($data['product_id']);
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);
        $requestedQty = (int) $data['quantity'];

        // 1. Validasi Kepemilikan (Tidak boleh menyewa barang sendiri)
        if ($product->user_id === $userId) {
            throw ValidationException::withMessages([
                'product_id' => ['Anda tidak dapat menyewa barang milik sendiri.'],
            ]);
        }

        // 2. Cek Ketersediaan Stok (Availability Check)
        if (!$this->checkAvailability($product, $startDate, $endDate, $requestedQty)) {
            throw ValidationException::withMessages([
                'quantity' => ['Stok barang tidak mencukupi untuk tanggal yang dipilih.'],
            ]);
        }

        // 3. Hitung Durasi & Total Harga
        // include start date (misal tgl 1 s/d 2 = 2 hari)
        $durationInDays = $startDate->diffInDays($endDate) + 1;
        $totalPrice = $product->price_per_day * $durationInDays * $requestedQty;

        // DB Transaction Start (Disarankan pakai DB::transaction)
        // 4. Buat Transaksi
        $rental = Rental::create([
            'user_id' => $userId,
            'product_id' => $product->id,
            'quantity' => $requestedQty,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'status' => 'pending',
            'total_price' => $totalPrice,
        ]);

        // 5. Generate Midtrans Snap Token
        // Kita instansiasi PaymentService (atau bisa via dependency injection di constructor)
        $paymentService = new PaymentService();
        $snapToken = $paymentService->createSnapToken($rental->load(['product', 'renter']));

        // 6. Simpan Token ke Database
        $rental->update(['snap_token' => $snapToken]);
        return $rental;
    }

    /**
     * Get History for Renter (Barang yang saya sewa)
     */
    public function getUserRentals(int $userId)
    {
        return Rental::with(['product.images', 'product.lender', 'renter'])
            ->where('user_id', $userId)
            ->latest()
            ->paginate(10);
    }

    /**
     * Get Incoming Orders for Lender (Barang saya yang disewa orang)
     */
    public function getLenderOrders(int $lenderId)
    {
        // Query sedikit kompleks: Cari Rental dimana Product-nya milik Lender ini
        return Rental::with(['product.images', 'renter'])
            ->whereHas('product', function ($query) use ($lenderId) {
                $query->where('user_id', $lenderId);
            })
            ->latest()
            ->paginate(10);
    }

    /**
     * Get Detail Rental (Untuk halaman detail invoice)
     */
    public function getRentalDetail(int $rentalId, int $userId)
    {
        $rental = Rental::with(['product.images', 'product.lender', 'renter'])
            ->findOrFail($rentalId);

        // Validasi Akses: Hanya Renter YBS atau Lender YBS yang boleh lihat
        if ($rental->user_id !== $userId && $rental->product->user_id !== $userId) {
            throw ValidationException::withMessages([
                'id' => ['Anda tidak memiliki akses ke data peminjaman ini.'],
            ]);
        }

        return $rental;
    }

    /**
     * Logika Inti Anti-Bentrokan Jadwal
     */
    private function checkAvailability(Product $product, Carbon $start, Carbon $end, int $qty): bool
    {
        // Ambil semua rental untuk produk ini yang statusnya AKTIF (bukan cancelled/completed/rejected)
        // Dan tanggalnya beririsan (overlap) dengan request user
        $activeRentals = Rental::where('product_id', $product->id)
            ->whereIn('status', ['pending', 'paid', 'active']) // Status yang mengurangi stok
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                      ->orWhereBetween('end_date', [$start, $end])
                      ->orWhere(function ($q) use ($start, $end) {
                          $q->where('start_date', '<', $start)
                            ->where('end_date', '>', $end);
                      });
            })
            ->sum('quantity');

        // Stok Sisa = Stok Total - Stok Terpakai
        $remainingStock = $product->stock - $activeRentals;

        return $remainingStock >= $qty;
    }

    /**
     * Proses Pengembalian Barang (Scan QR Result)
     */
    public function returnRental(int $rentalId, int $lenderId): Rental
    {
        // 1. Validasi: Pastikan yang memproses return adalah Pemilik Barang (Lender)
        $rental = Rental::with('product')->where('id', $rentalId)->firstOrFail();

        if ($rental->product->user_id !== $lenderId) {
            throw ValidationException::withMessages([
                'id' => ['Anda tidak berhak memproses pengembalian barang ini.'],
            ]);
        }

        if ($rental->status !== 'active') { // Hanya barang status 'active' yang bisa dikembalikan
             throw ValidationException::withMessages([
                'status' => ['Barang ini belum diambil atau sudah dikembalikan.'],
            ]);
        }

        // 2. Hitung Denda (Logic Standard: 1 Hari Telat = 1x Harga Sewa Harian)
        $actualReturnDate = now();
        $scheduleEndDate = Carbon::parse($rental->end_date)->endOfDay(); // Toleransi sampai jam 23:59

        $fineTotal = 0;
        $fineNotes = null;
        $fineStatus = null;

        // Cek apakah tanggal kembali > jadwal selesai?
        if ($actualReturnDate->gt($scheduleEndDate)) {
            // Hitung selisih dalam float (misal 1.5 hari), lalu absolutekan, lalu bulatkan ke atas
            // Contoh: Telat 2 jam = 0.1 hari -> Dibulatkan jadi 1 hari denda
            $lateDays = (int) ceil(abs($scheduleEndDate->floatDiffInDays($actualReturnDate)));

            // Jaga-jaga jika hasilnya 0 (misal telat hitungan detik), tetap set 1
            if ($lateDays < 1) $lateDays = 1;

            $fineTotal = $lateDays * $rental->product->price_per_day * $rental->quantity;
            $fineNotes = "Terlambat $lateDays hari. Denda Rp " . number_format($fineTotal, 0, ',', '.');
            $fineStatus = 'unpaid';
        }

        // 3. Update Transaksi
        $rental->update([
            'status' => 'completed', // Sewa selesai (Jika ada denda, user bayar terpisah nanti)
            'actual_return_date' => $actualReturnDate,
            'fine_total' => $fineTotal,
            'fine_notes' => $fineNotes,
            'fine_status' => $fineStatus,
        ]);

        return $rental;
    }
}
