<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rental extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'start_date',
        'end_date',
        'status',          // 'pending', 'paid', 'active', 'completed', 'cancelled'
        'total_price',
        'snap_token',
        'actual_return_date',
        'fine_total',
        'fine_notes',
        'fine_status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'actual_return_date' => 'datetime',
        'total_price' => 'decimal:2',
        'fine_total' => 'decimal:2',
    ];

    // --- RELATIONSHIPS ---

    // Siapa yang menyewa (Renter)
    public function renter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Barang apa yang disewa
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
