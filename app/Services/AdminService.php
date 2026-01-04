<?php

namespace App\Services;

use App\Models\User;

class AdminService
{
    /**
     * Ambil list user dengan prioritas sorting:
     * 1. Pending (Paling atas)
     * 2. Verified
     * 3. Rejected
     * 4. Belum Upload (None)
     */
    public function getUsersList(int $perPage = 10)
    {
        return User::where('role', 'user')
            ->orderByRaw("FIELD(kyc_status, 'pending', 'verified', 'rejected', 'none')")
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Logic Verifikasi User
     */
    public function verifyUser(int $userId): User
    {
        $user = User::findOrFail($userId);

        $user->update([
            'kyc_status' => 'verified',
            'is_verified' => true, // Update flag lama juga biar aman
        ]);

        // Disini tempat logic tambahan masa depan (misal: Kirim Email Selamat)

        return $user;
    }

    /**
     * Logic Tolak User
     */
    public function rejectUser(int $userId): User
    {
        $user = User::findOrFail($userId);

        $user->update([
            'kyc_status' => 'rejected',
            'is_verified' => false,
        ]);

        // Disini tempat logic tambahan (misal: Kirim Email Alasan Penolakan)

        return $user;
    }
}
