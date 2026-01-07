<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Product\StoreProductRequest;
use App\Http\Requests\Api\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    // GET /api/products (Public)
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['category', 'search', 'lender_id']);
        $products = $this->productService->getProducts($filters);

        return response()->json([
            'message' => 'List produk berhasil diambil',
            'data' => ProductResource::collection($products)->resolve(),
        ]);
    }

    // POST /api/products (Lender Only)
    public function store(StoreProductRequest $request): JsonResponse
    {
        // Validasi sudah otomatis dihandle StoreProductRequest
        $data = $request->validated();

        // Eksekusi logic di Service
        $product = $this->productService->createProduct($data, $request->user()->id);

        return response()->json([
            'message' => 'Produk berhasil ditambahkan',
            'data' => new ProductResource($product),
        ], 201);
    }

    // GET /api/products/{slug} (Public)
    public function show(string $slug): JsonResponse
    {
        $product = Product::with(['images', 'lender'])->where('slug', $slug)->firstOrFail();

        return response()->json([
            'message' => 'Detail produk berhasil diambil',
            'data' => new ProductResource($product),
        ]);
    }

    // PUT /api/products/{id} (Lender Only)
    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        $product = Product::where('user_id', $request->user()->id)->findOrFail($id);

        $updatedProduct = $this->productService->updateProduct($product, $request->validated());

        return response()->json([
            'message' => 'Produk berhasil diperbarui',
            'data' => new ProductResource($updatedProduct),
        ]);
    }

    // DELETE /api/products/{id} (Lender Only)
    public function destroy(Request $request, string $id): JsonResponse
    {
        // Panggil Service untuk logic pembersihan
        $this->productService->deleteProduct((int)$id, (int)$request->user()->id);

        return response()->json([
            'message' => 'Produk dan gambar berhasil dihapus',
        ]);
    }
}
