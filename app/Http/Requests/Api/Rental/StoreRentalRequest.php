<?php

namespace App\Http\Requests\Api\Rental;

use Illuminate\Foundation\Http\FormRequest;

class StoreRentalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            // Tanggal mulai minimal hari ini
            'start_date' => 'required|date|after_or_equal:today',
            // Tanggal selesai wajib setelah tanggal mulai
            'end_date' => 'required|date|after_or_equal:start_date',
        ];
    }
}
