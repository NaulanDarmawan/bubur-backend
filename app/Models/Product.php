<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'category',
        'price_per_day',
        'stock',
        'is_available',
    ];

    protected $casts = [
        'price_per_day' => 'decimal:2',
        'stock' => 'integer',
        'is_available' => 'boolean',
    ];

    // --- RELATIONSHIPS ---

    // Pemilik barang (Lender)
    public function lender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Foto-foto barang
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    // Riwayat penyewaan barang ini
    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }
}
