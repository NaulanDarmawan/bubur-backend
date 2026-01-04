<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'required|string',
        ]);

        $result = $this->authService->register($validated);

        return response()->json([
            'message' => 'Registrasi berhasil',
            'data' => $result,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $result = $this->authService->login($request->email, $request->password);

        return response()->json([
            'message' => 'Login berhasil',
            'data' => $result,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'message' => 'Logout berhasil',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Profile user berhasil diambil',
            'data' => $request->user(),
        ]);
    }

    // POST /api/kyc
    public function uploadKyc(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'ktp_nik' => 'required|numeric|digits:16', // Wajib 16 angka
            'ktp_image' => 'required|image|mimes:jpeg,png,jpg', // Max 3MB
        ]);

        $user = $request->user();

        // Cek apakah sudah verified? Kalau sudah, jangan upload lagi
        if ($user->kyc_status === 'verified') {
            return response()->json(['message' => 'Akun Anda sudah terverifikasi.'], 400);
        }

        // 2. Upload File
        if ($request->hasFile('ktp_image')) {
            // Simpan ke folder 'storage/app/public/kyc'
            $path = $request->file('ktp_image')->store('kyc', 'public');

            // Dapatkan URL publiknya
            // Pastikan Anda sudah jalankan: php artisan storage:link
            $url = asset('storage/' . $path);

            // 3. Update Database
            $user->update([
                'ktp_nik' => $request->ktp_nik,
                'ktp_image_url' => 'storage/' . $path, // Simpan path relatif atau full URL
                'kyc_status' => 'pending', // Ubah status jadi pending biar muncul di admin
            ]);
        }

        return response()->json([
            'message' => 'KTP berhasil diupload. Mohon tunggu verifikasi Admin.',
            'data' => $user,
        ]);
    }
}
