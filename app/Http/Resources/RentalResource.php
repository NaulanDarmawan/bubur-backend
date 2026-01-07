<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status, // pending, paid, active, etc
            'total_price' => (float) $this->total_price,
            'price_format' => 'Rp ' . number_format($this->total_price, 0, ',', '.'),

            // Detail Waktu
            'start_date' => $this->start_date->format('d M Y'),
            'end_date' => $this->end_date->format('d M Y'),
            'duration_days' => $this->start_date->diffInDays($this->end_date) + 1,
            'is_overdue' => $this->status === 'active' && now()->gt($this->end_date),

            'snap_token' => $this->snap_token,
            'fine_total' => (float) $this->fine_total,
            'fine_status' => $this->fine_status,
            'actual_return_date' => $this->actual_return_date,

            // Relasi Produk (Barang apa yang disewa?)
            'product' => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'slug' => $this->product->slug,
                'image' => $this->product->images->first()->image_url ?? null, // Ambil gambar pertama saja
                'lender_name' => $this->product->lender->name, // Siapa pemiliknya
            ],

            // Relasi User (Siapa yang menyewa? - Penting untuk Lender)
            'renter' => [
                'id' => $this->renter->id,
                'name' => $this->renter->name,
                'phone' => $this->renter->phone,
                'avatar' => $this->renter->avatar_url,
            ],

            'created_at' => $this->created_at->diffForHumans(),
        ];
    }
}
