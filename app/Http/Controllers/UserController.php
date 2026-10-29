<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return view('masters.index', [
            'title' => 'Pengguna', 'type' => 'users', 'rows' => User::orderBy('name')->paginate(12),
            'columns' => ['name' => 'Nama', 'email' => 'Email', 'role' => 'Peran'],
        ]);
    }

    public function create()
    {
        return view('masters.form', ['title' => 'Pengguna', 'type' => 'users', 'row' => new User]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'], 'role' => ['required', Rule::in(['admin', 'staff', 'customer'])],
        ]);
        $data['password'] = Hash::make($data['password']);
        User::create($data);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('masters.form', ['title' => 'Pengguna', 'type' => 'users', 'row' => $user]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', Rule::in(['admin', 'staff', 'customer'])],
        ]);
        if ($user->is($request->user()) && $data['role'] !== 'admin') {
            return back()->withErrors(['role' => 'Anda tidak dapat menghapus hak admin dari akun sendiri.'])->withInput();
        }
        if (filled($data['password'] ?? null)) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['delete' => 'Anda tidak dapat menghapus akun sendiri.']);
        }
        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return back()->withErrors(['delete' => 'Minimal harus ada satu akun admin.']);
        }
        if ($user->purchases()->exists() || $user->sales()->exists()) {
            return back()->withErrors(['delete' => 'Pengguna memiliki riwayat transaksi dan tidak dapat dihapus.']);
        }
        $user->delete();

        return back()->with('success', 'Pengguna berhasil dihapus.');
    }
}
