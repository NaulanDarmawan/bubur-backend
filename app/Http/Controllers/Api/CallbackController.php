<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CallbackController extends Controller
{
    public function midtransWebhook(Request $request)
    {
        try {
            // 1. Tangkap Payload Langsung dari Laravel Request (Lebih Aman)
            $payload = $request->all();

            Log::info('Midtrans Webhook Payload:', $payload); // Debugging: Cek log jika error lagi

            $transaction = $payload['transaction_status'] ?? null;
            $type = $payload['payment_type'] ?? null;
            $orderId = $payload['order_id'] ?? null;
            $fraud = $payload['fraud_status'] ?? null;

            // 2. Validasi ID
            if (!$orderId) {
                return response()->json(['message' => 'Invalid Order ID'], 400);
            }

            // 3. Extract Rental ID
            // Format: RENTAL-{id}-{timestamp} -> Ambil angka di tengah
            $parts = explode('-', $orderId);

            // Validasi format order_id agar tidak error offset
            if (count($parts) < 2) {
                return response()->json(['message' => 'Invalid Order ID Format'], 400);
            }

            $rentalId = $parts[1];
            $rental = Rental::findOrFail($rentalId);

            // 4. Logic Update Status
            if ($transaction == 'capture') {
                if ($type == 'credit_card') {
                    if ($fraud == 'challenge') {
                        $rental->update(['status' => 'pending']);
                    } else {
                        $rental->update(['status' => 'paid']);
                    }
                }
            } else if ($transaction == 'settlement') {
                $rental->update(['status' => 'paid']);
            } else if ($transaction == 'pending') {
                $rental->update(['status' => 'pending']);
            } else if ($transaction == 'deny') {
                $rental->update(['status' => 'cancelled']);
            } else if ($transaction == 'expire') {
                $rental->update(['status' => 'cancelled']);
            } else if ($transaction == 'cancel') {
                $rental->update(['status' => 'cancelled']);
            }

            return response()->json([
                'message' => 'Callback processed successfully',
                'new_status' => $rental->status
            ]);

        } catch (\Exception $e) {
            Log::error('Midtrans Callback Error: ' . $e->getMessage());
            return response()->json(['message' => 'Error processing callback: ' . $e->getMessage()], 500);
        }
    }
}
