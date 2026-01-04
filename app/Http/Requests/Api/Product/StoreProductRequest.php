<?php

namespace App\Http\Requests\Api\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // User yang login boleh akses
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|in:Photography,Gaming,Outdoor,Electronics,Others', // Bisa disesuaikan
            'price_per_day' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:1',
            // Validasi Gambar (Array of files)
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg',
        ];
    }
}
