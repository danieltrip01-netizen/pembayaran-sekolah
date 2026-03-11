<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Setoran;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('role')->orderBy('name')->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:100',
            'nama_lengkap' => 'required|string|max:100',
            'email'        => 'required|email|unique:users',
            'password'     => 'required|min:8|confirmed',
            'role'         => 'required|in:admin_yayasan,admin_tk,admin_sd,admin_smp',
        ]);

        User::create([
            'name'         => $request->name,
            'nama_lengkap' => $request->nama_lengkap,
            'email'        => $request->email,
            'password'     => Hash::make($request->password),
            'role'         => $request->role,
        ]);

        return redirect()->route('admin.users.index')
                         ->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'         => 'required|string|max:100',
            'nama_lengkap' => 'required|string|max:100',
            'email'        => 'required|email|unique:users,email,' . $user->id,
            'role'         => 'required|in:admin_yayasan,admin_tk,admin_sd,admin_smp',
            'password'     => 'nullable|min:8|confirmed',
        ]);

        $data = $request->only(['name', 'nama_lengkap', 'email', 'role']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
                         ->with('success', 'Data user berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')
                         ->with('success', 'User berhasil dihapus.');
    }

    /**
     * Reset password user ke password default.
     * POST /admin/users/{user}/reset-password
     */
    public function resetPassword(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat mereset password akun sendiri.');
        }

        // Default password: 8 karakter pertama email + angka tahun ini
        $defaultPassword = substr($user->email, 0, 8) . date('Y');

        $user->update(['password' => Hash::make($defaultPassword)]);

        return back()->with('success',
            "Password {$user->name} berhasil direset. Password baru: {$defaultPassword}"
        );
    }
    // ── Show ─────────────────────────────────────────────────────────

    public function show(User $user)
    {
        $totalPembayaran = Pembayaran::where('user_id', $user->id)->count();

        $totalSetoran = Setoran::where('user_id', $user->id)->count();

        $bulanIni = Pembayaran::where('user_id', $user->id)
            ->whereYear('tanggal_bayar',  now()->year)
            ->whereMonth('tanggal_bayar', now()->month)
            ->count();

        $recentPembayaran = Pembayaran::where('user_id', $user->id)
            ->with('siswa')
            ->orderByDesc('tanggal_bayar')
            ->limit(5)
            ->get();

        return view('admin.users.show', compact(
            'user',
            'totalPembayaran',
            'totalSetoran',
            'bulanIni',
            'recentPembayaran'
        ));
    }
}