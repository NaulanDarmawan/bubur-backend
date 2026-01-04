<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Rental\StoreRentalRequest;
use App\Services\RentalService;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\RentalResource;
use Illuminate\Http\Request;

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
            'data' => RentalResource::collection($rentals)->response()->getData(true),
        ]);
    }

    // GET /api/lender/orders (Lender Dashboard)
    public function lenderOrders(Request $request): JsonResponse
    {
        $orders = $this->rentalService->getLenderOrders((int) $request->user()->id);

        return response()->json([
            'message' => 'Daftar pesanan masuk berhasil diambil',
            'data' => RentalResource::collection($orders)->response()->getData(true),
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
}
