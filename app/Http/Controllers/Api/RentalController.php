<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Rental\StoreRentalRequest;
use App\Services\RentalService;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\RentalResource;
use Illuminate\Http\Request;
use App\Models\Rental;

class RentalController extends Controller
{
    protected RentalService $rentalService;

    public function __construct(RentalService $rentalService)
    {
        $this->rentalService = $rentalService;
    }

    // POST /api/rentals (Checkout)
    public function store(StoreRentalRequest $request): JsonResponse
    {
        $rental = $this->rentalService->createRental(
            $request->validated(),
            (int) $request->user()->id
        );

        return response()->json([
            'message' => 'Booking berhasil dibuat. Silakan lanjut ke pembayaran.',
            'data' => $rental,
        ], 201);
    }

    // GET /api/rentals (Renter History)
    public function index(Request $request): JsonResponse
    {
        $rentals = $this->rentalService->getUserRentals((int) $request->user()->id);

        return response()->json([
            'message' => 'History penyewaan berhasil diambil',
            'data' => RentalResource::collection($rentals)->resolve(),
        ]);
    }

    // GET /api/lender/orders (Lender Dashboard)
    public function lenderOrders(Request $request): JsonResponse
    {
        $user = $request->user();

        // Cari rental dimana produknya milik user yang sedang login
        $orders = Rental::whereHas('product', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['product', 'user']) // Load data produk & penyewa
        ->latest()
        ->get();

        return response()->json([
            'message' => 'Data pesanan masuk berhasil diambil',
            'data' => RentalResource::collection($orders)->resolve()
        ]);
    }

    // GET /api/rentals/{id} (Detail Transaksi)
    public function show(Request $request, string $id): JsonResponse
    {
        $rental = $this->rentalService->getRentalDetail((int) $id, (int) $request->user()->id);

        return response()->json([
            'message' => 'Detail peminjaman berhasil diambil',
            'data' => new RentalResource($rental),
        ]);
    }

    // 2. SERAH TERIMA BARANG (HANDOVER)
    // Mengubah status dari 'paid' -> 'active'
    public function startRental(Request $request, $id): JsonResponse
    {
        $rental = Rental::findOrFail($id);
        $user = $request->user();

        // Validasi: Pastikan yang akses adalah Pemilik Barang (Lender)
        if ($rental->product->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Validasi: Hanya bisa start kalau statusnya 'paid'
        if ($rental->status !== 'paid') {
            return response()->json(['message' => 'Pesanan belum dibayar atau sudah berjalan'], 400);
        }

        // Update Status & Simpan info QR Unit (jika ada)
        $rental->update([
            'status' => 'active',
            // Kita simpan kode unit di kolom notes/deskripsi sementara (MVP)
            // Atau kalau tabel rentals belum ada kolom khusus, abaikan dulu unit_serial-nya
            // 'notes' => 'Unit Serial: ' . $request->unit_serial 
        ]);

        return response()->json([
            'message' => 'Barang berhasil diserahkan. Masa sewa dimulai!',
            'data' => new RentalResource($rental)
        ]);
    }

    // POST /api/rentals/{id}/return (Lender Only - Scan QR)
    public function returnProduct(Request $request, string $id): JsonResponse
    {
        $rental = $this->rentalService->returnRental((int) $id, (int) $request->user()->id);

        return response()->json([
            'message' => 'Barang berhasil dikembalikan.',
            'data' => new RentalResource($rental),
        ]);
    }
}
