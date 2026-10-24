<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = User::query();

        // Filter: Pencarian kata kunci (nama, email, no whatsapp/phone)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter: Role
        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        // Filter: Status Akun
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $users = $query->latest()->paginate(10)->withQueryString();

        // Ringkasan Statistik
        $stats = [
            'total' => User::count(),
            'admin' => User::where('role', 'admin')->count(),
            'agency_partner' => User::where('role', 'agency_partner')->count(),
            'buyer' => User::where('role', 'buyer')->count(),
            'active' => User::where('status', 'active')->count(),
            'pending' => User::where('status', 'pending')->count(),
            'suspended' => User::where('status', 'suspended')->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['nullable', ...User::phoneRules(), 'unique:users,phone'],
            // Admin tidak dapat mendaftarkan akun admin baru
            'role' => ['required', 'string', Rule::in(['agency_partner', 'buyer'])],
            'status' => ['required', 'string', Rule::in(['active', 'pending', 'suspended'])],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'status' => $validated['status'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Pengguna '{$user->name}' berhasil ditambahkan ke dalam sistem.");
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): View
    {
        $user->load(['agencyPartner', 'buyerLeads']);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:150', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', ...User::phoneRules(), Rule::unique('users')->ignore($user->id)],
            // Akun non-admin tidak dapat dinaikkan menjadi admin
            'role' => ['required', 'string', Rule::in($user->isAdmin() ? ['admin', 'agency_partner', 'buyer'] : ['agency_partner', 'buyer'])],
            'status' => ['required', 'string', Rule::in(['active', 'pending', 'suspended'])],
            'password' => ['nullable', 'string', 'confirmed', Password::defaults()],
        ]);

        // Pencegahan: Super Admin yang sedang login tidak sengaja mendemosikan dirinya sendiri jika satu-satunya admin
        if ($user->id === Auth::id() && $validated['role'] !== 'admin') {
            return back()
                ->withInput()
                ->with('error', 'Anda tidak dapat mengubah peran akun Anda sendiri dari Super Admin.');
        }

        // Pencegahan: Super Admin yang sedang login tidak sengaja menonaktifkan dirinya sendiri
        if ($user->id === Auth::id() && $validated['status'] !== 'active') {
            return back()
                ->withInput()
                ->with('error', 'Anda tidak dapat mengubah status akun Anda sendiri menjadi nonaktif/suspended.');
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;
        $user->role = $validated['role'];
        $user->status = $validated['status'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Data pengguna '{$user->name}' berhasil diperbarui.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Proteksi: Tidak boleh menghapus akun sendiri
        if ($user->id === Auth::id()) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Tindakan ditolak: Anda tidak dapat menghapus akun Super Admin Anda sendiri.');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Pengguna '{$userName}' telah berhasil dihapus dari sistem.");
    }
}
