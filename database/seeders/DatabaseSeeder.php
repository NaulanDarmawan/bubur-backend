<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create ADMIN Account
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@bubur.com',
            'password' => Hash::make('password'), // Password default
            'phone' => '081234567890',
            'role' => 'admin',
            'kyc_status' => 'verified',
            'is_verified' => true,
        ]);

        // 2. Create LENDER Account (Penyewa Biasa)
        $lender = User::create([
            'name' => 'Budi Lender',
            'email' => 'budi@bubur.com',
            'password' => Hash::make('password'),
            'phone' => '089876543210',
            'role' => 'user',
            'kyc_status' => 'verified',
            'is_verified' => true,
            'ktp_nik' => '3578120000000001',
        ]);

        // 3. Create PRODUCTS (Milik Budi)

        // Produk 1: Kamera
        $camera = Product::create([
            'user_id' => $lender->id,
            'name' => 'Sony Alpha A7 III Kit Lens',
            'slug' => 'sony-alpha-a7-iii-kit-lens-' . uniqid(),
            'description' => 'Kamera mirrorless full-frame, kondisi 99% mulus. Cocok untuk videografi.',
            'category' => 'Photography',
            'price_per_day' => 250000.00,
            'stock' => 1,
            'is_available' => true,
        ]);

        ProductImage::create([
            'product_id' => $camera->id,
            'image_url' => 'https://placehold.co/600x400/png?text=Sony+A7',
        ]);

        // Produk 2: Console Game
        $ps5 = Product::create([
            'user_id' => $lender->id,
            'name' => 'PlayStation 5 Disc Edition + 2 Stick',
            'slug' => 'ps5-disc-edition-' . uniqid(),
            'description' => 'Sewa PS5 harian. Game ada FIFA 24, Spider-Man 2, God of War.',
            'category' => 'Gaming',
            'price_per_day' => 150000.00,
            'stock' => 2,
            'is_available' => true,
        ]);

        ProductImage::create([
            'product_id' => $ps5->id,
            'image_url' => 'https://placehold.co/600x400/png?text=PS5',
        ]);

        // Produk 3: Tenda Camping
        $tenda = Product::create([
            'user_id' => $lender->id,
            'name' => 'Tenda Eiger 4 Person Waterproof',
            'slug' => 'tenda-eiger-4p-' . uniqid(),
            'description' => 'Tenda camping kapasitas 4 orang, double layer, anti badai.',
            'category' => 'Outdoor',
            'price_per_day' => 50000.00,
            'stock' => 5,
            'is_available' => true,
        ]);

        ProductImage::create([
            'product_id' => $tenda->id,
            'image_url' => 'https://placehold.co/600x400/png?text=Tenda',
        ]);
    }
}
