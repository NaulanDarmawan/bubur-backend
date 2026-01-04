<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'category' => $this->category,
            'description' => $this->description,
            'price_per_day' => (float) $this->price_per_day,
            'price_format' => 'Rp ' . number_format($this->price_per_day, 0, ',', '.'),
            'stock' => (int) $this->stock,
            'is_available' => (boolean) $this->is_available,
            // Relationship: Ambil URL gambar
            'images' => $this->images->map(fn($img) => $img->image_url),
            // Relationship: Info pemilik (Lender) ringkas saja
            'lender' => [
                'id' => $this->lender->id,
                'name' => $this->lender->name,
                'avatar' => $this->lender->avatar_url,
                'is_verified' => $this->lender->is_verified,
            ],
            'created_at' => $this->created_at->diffForHumans(),
        ];
    }
}
