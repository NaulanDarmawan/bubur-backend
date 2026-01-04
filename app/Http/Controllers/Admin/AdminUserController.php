<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminService; // Langsung panggil Service, tanpa Interface
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    protected $adminService;

    // Dependency Injection Service
    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function index()
    {
        // Panggil logic query dari service
        $users = $this->adminService->getUsersList();

        // Return ke View yang tadi sudah kita percantik
        return view('admin.users.index', compact('users'));
    }

    public function approveKyc($id)
    {
        $this->adminService->verifyUser($id);

        return redirect()->back()->with('success', "User berhasil diverifikasi.");
    }

    public function rejectKyc($id)
    {
        $this->adminService->rejectUser($id);

        return redirect()->back()->with('success', "Verifikasi user ditolak."); // Saya ganti jadi success alert biar UI hijau, atau bisa error
    }
}
