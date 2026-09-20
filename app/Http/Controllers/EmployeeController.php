<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class EmployeeController extends Controller
{
    /**
     * Daftarkan staf baru ke toko pemilik saat ini.
     */
    public function store(Request $request)
    {
        $currentUser = auth()->user();
        if (!$currentUser->isOwner()) {
            abort(403, 'Hanya pemilik toko yang dapat mengelola staf karyawan.');
        }

        if (!$currentUser->canAddEmployee()) {
            return back()->with('error', 'Batas kuota akun staf untuk paket Anda telah tercapai. Silakan upgrade ke paket Pro atau Enterprise untuk menambah staf.');
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'role'     => 'required|in:admin_cs,production',
            'password' => ['required', 'string', Password::min(8)],
        ]);

        User::create([
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'password'      => Hash::make($validated['password']),
            'role'          => $validated['role'],
            'owner_id'      => $currentUser->id,
            'business_name' => $currentUser->business_name,
            'phone'         => null,
        ]);

        return back()->with('success', 'Akun staf berhasil dibuat dan dapat langsung login.');
    }

    /**
     * Hapus / cabut akses akun staf.
     */
    public function destroy(User $employee)
    {
        $currentUser = auth()->user();
        if (!$currentUser->isOwner() || $employee->owner_id !== $currentUser->id) {
            abort(403, 'Anda tidak memiliki hak untuk menghapus akun staf ini.');
        }

        $employee->delete();

        return back()->with('success', 'Akun staf berhasil dihapus dari sistem toko.');
    }
}
