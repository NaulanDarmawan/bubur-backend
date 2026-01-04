<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Rental;
use Midtrans\Config;
use Midtrans\Snap;

class PaymentService
{
    public function __construct()
    {
        // Set konfigurasi Midtrans saat Service ini dipanggil
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = config('services.midtrans.is_sanitized');
        Config::$is3ds = config('services.midtrans.is_3ds');
    }

    public function createSnapToken(Rental $rental): string
    {
        // 1. Siapkan Parameter Transaksi (Wajib ada order_id & gross_amount)
        $params = [
            'transaction_details' => [
                'order_id' => 'RENTAL-' . $rental->id . '-' . time(), // ID Unik (tambah timestamp biar aman saat tes ulang)
                'gross_amount' => (int) $rental->total_price,
            ],
            'item_details' => [
                [
                    'id' => 'PROD-' . $rental->product_id,
                    'price' => (int) $rental->total_price,
                    'quantity' => 1,
                    'name' => 'Sewa: ' . substr($rental->product->name, 0, 40), // Midtrans batasi panjang nama
                ]
            ],
            'customer_details' => [
                'first_name' => $rental->renter->name,
                'email' => $rental->renter->email,
                'phone' => $rental->renter->phone,
            ],
        ];

        // 2. Minta Token ke Midtrans
        $snapToken = Snap::getSnapToken($params);

        return $snapToken;
    }
}
