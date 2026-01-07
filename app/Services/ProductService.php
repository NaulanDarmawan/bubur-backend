<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    /**
     * Mengambil daftar produk (bisa untuk Homepage atau Search)
     */
    public function getProducts(array $filters = [])
    {
        $query = Product::with(['images', 'lender'])->where('is_available', true);

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        // return $query->latest()->paginate(10);
        return $query->latest()->get();
    }

    /**
     * Logic inti untuk Lender upload barang + Gambar
     */
    public function createProduct(array $data, int $userId): Product
    {
        return DB::transaction(function () use ($data, $userId) {
            // 1. Simpan Data Produk Utama
            $product = Product::create([
                'user_id' => $userId,
                'name' => $data['name'],
                'slug' => Str::slug($data['name']) . '-' . uniqid(),
                'description' => $data['description'],
                'category' => $data['category'],
                'price_per_day' => $data['price_per_day'],
                'stock' => $data['stock'],
                'is_available' => true,
            ]);

            // 2. Handle Multiple Image Uploads
            if (isset($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $image) {
                    $this->uploadProductImage($product, $image);
                }
            }

            return $product->load('images');
        });
    }

   /**
     * Logic update barang (Termasuk replace gambar jika ada input gambar baru)
     */
    public function updateProduct(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            // 1. Update Data Teks Standar
            $product->update(\Illuminate\Support\Arr::except($data, ['images']));

            // 2. Cek apakah ada request untuk update gambar?
            if (isset($data['images']) && is_array($data['images'])) {
                // STRATEGI: FULL REPLACE/SYNC
                // A. Hapus dulu semua gambar fisik lama dari storage
                foreach ($product->images as $oldImage) {
                    $path = str_replace('/storage/', '', $oldImage->image_url);
                    if (Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                }

                // B. Hapus record gambar lama di database
                $product->images()->delete();

                // C. Upload gambar-gambar baru
                foreach ($data['images'] as $newImage) {
                    $this->uploadProductImage($product, $newImage);
                }
            }

            // Return produk fresh dengan relasi gambar terbaru
            return $product->refresh()->load('images');
        });
    }

    /**
     * Hapus Produk beserta File Gambar fisiknya
     */
    public function deleteProduct(int $productId, int $userId): void
    {
        $product = Product::where('user_id', $userId)->findOrFail($productId);

        // 1. Hapus File Fisik di Storage
        foreach ($product->images as $image) {
            // Kita perlu mengambil path relatif dari URL.
            // URL: /storage/products/abc.jpg -> Path: products/abc.jpg
            $path = str_replace('/storage/', '', $image->image_url);

            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        // 2. Hapus Data di Database (Cascade akan menghapus product_images juga)
        $product->delete();
    }

    /**
     * Helper untuk upload fisik gambar dan simpan ke table product_images
     */
    private function uploadProductImage(Product $product, UploadedFile $file): void
    {
        // Simpan ke storage/app/public/products
        $path = $file->store('products', 'public');

        // Generate URL publik (misal: /storage/products/namafile.jpg)
        $url = Storage::url($path);

        // Simpan ke database
        ProductImage::create([
            'product_id' => $product->id,
            'image_url' => $url, // Kita simpan path relatifnya agar domain fleksibel
        ]);
    }
}
