<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\Rental;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class RentalService
{
    public function createRental(array $data, int $userId): Rental
    {
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

        // 4. Buat Transaksi
        return Rental::create([
            'user_id' => $userId,
            'product_id' => $product->id,
            'quantity' => $requestedQty,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'status' => 'pending', // Menunggu pembayaran
            'total_price' => $totalPrice,
            // Field lain null dulu (denda, return date, dll)
        ]);
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
}
