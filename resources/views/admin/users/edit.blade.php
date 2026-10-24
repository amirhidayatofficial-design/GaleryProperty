<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                    <a href="{{ route('admin.users.index') }}" class="hover:text-primary-600 transition">Manajemen Pengguna</a>
                    <span>/</span>
                    <span class="text-gray-800 font-medium">Ubah Pengguna</span>
                </div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    Ubah Data: {{ $user->name }}
                </h2>
            </div>
            <div>
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('error'))
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start gap-3 shadow-sm">
                    <svg class="w-5 h-5 text-rose-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1 text-sm font-medium">
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6" data-confirm-leave>
                    @csrf
                    @method('PUT')

                    {{-- Nama Lengkap --}}
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">
                            Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name', $user->name) }}"
                            required
                            class="w-full text-sm border-gray-200 rounded-lg focus:ring-primary-500 focus:border-primary-500 @error('name') border-rose-400 ring-rose-200 @enderror"
                        />
                        @error('name')
                            <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Grid: Email & Telepon / WhatsApp --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">
                                Alamat Email <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                value="{{ old('email', $user->email) }}"
                                required
                                class="w-full text-sm border-gray-200 rounded-lg focus:ring-primary-500 focus:border-primary-500 @error('email') border-rose-400 ring-rose-200 @enderror"
                            />
                            @error('email')
                                <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-semibold text-gray-700 mb-1">
                                Nomor WhatsApp / Telepon
                            </label>
                            <input
                                type="tel"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                minlength="10"
                                maxlength="15"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                name="phone"
                                id="phone"
                                value="{{ old('phone', $user->phone) }}"
                                placeholder="Contoh: 081234567890"
                                class="w-full text-sm border-gray-200 rounded-lg focus:ring-primary-500 focus:border-primary-500 @error('phone') border-rose-400 ring-rose-200 @enderror"
                            />
                            @error('phone')
                                <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Grid: Peran (Role) & Status Akun --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="role" class="block text-sm font-semibold text-gray-700 mb-1">
                                Peran Akun (Role) <span class="text-rose-500">*</span>
                            </label>
                            <select
                                name="role"
                                id="role"
                                required
                                class="w-full text-sm border-gray-200 rounded-lg focus:ring-primary-500 focus:border-primary-500 text-gray-800 @error('role') border-rose-400 ring-rose-200 @enderror"
                            >
                                <option value="buyer" {{ old('role', $user->role) === 'buyer' ? 'selected' : '' }}>Calon Pembeli (Keluarga Muslim)</option>
                                <option value="agency_partner" {{ old('role', $user->role) === 'agency_partner' ? 'selected' : '' }}>Mitra Agency Properti</option>
                                @if ($user->isAdmin())
                                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Super Admin (Internal)</option>
                                @endif
                            </select>
                            @if ($user->id === auth()->id())
                                <p class="text-xs text-amber-600 mt-1">Peran akun sendiri dilindungi dari penurunan hak akses.</p>
                            @endif
                            @error('role')
                                <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-semibold text-gray-700 mb-1">
                                Status Akun <span class="text-rose-500">*</span>
                            </label>
                            <select
                                name="status"
                                id="status"
                                required
                                class="w-full text-sm border-gray-200 rounded-lg focus:ring-primary-500 focus:border-primary-500 text-gray-800 @error('status') border-rose-400 ring-rose-200 @enderror"
                            >
                                <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Aktif (Dapat Bertransaksi)</option>
                                <option value="pending" {{ old('status', $user->status) === 'pending' ? 'selected' : '' }}>Pending (Menunggu Verifikasi)</option>
                                <option value="suspended" {{ old('status', $user->status) === 'suspended' ? 'selected' : '' }}>Suspended (Dinonaktifkan)</option>
                            </select>
                            @error('status')
                                <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Password Optional Section --}}
                    <div class="pt-4 border-t border-gray-100">
                        <div class="p-3 bg-amber-50/60 border border-amber-200 rounded-lg text-xs text-amber-800 mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Biarkan kolom kata sandi kosong jika tidak ingin mengubah password yang sudah ada.</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">
                                    Kata Sandi Baru (Opsional)
                                </label>
                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    placeholder="Isi hanya jika ingin diganti"
                                    class="w-full text-sm border-gray-200 rounded-lg focus:ring-primary-500 focus:border-primary-500 @error('password') border-rose-400 ring-rose-200 @enderror"
                                />
                                @error('password')
                                    <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1">
                                    Ulangi Kata Sandi Baru
                                </label>
                                <input
                                    type="password"
                                    name="password_confirmation"
                                    id="password_confirmation"
                                    placeholder="Ulangi kata sandi baru"
                                    class="w-full text-sm border-gray-200 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                                />
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
                        <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 text-sm font-semibold text-gray-600 hover:text-gray-800 transition">
                            Batal
                        </a>
                        <button type="submit" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
