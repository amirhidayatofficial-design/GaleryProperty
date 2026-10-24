<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                    <a href="{{ route('admin.users.index') }}" class="hover:text-primary-600 transition">Manajemen Pengguna</a>
                    <span>/</span>
                    <span class="text-gray-800 font-medium">Tambah Baru</span>
                </div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    Tambah Pengguna Baru
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
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6" data-confirm-leave>
                    @csrf

                    {{-- Nama Lengkap --}}
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">
                            Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name') }}"
                            placeholder="Contoh: Ahmad Fauzan"
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
                                value="{{ old('email') }}"
                                placeholder="ahmad@keluargamuslim.com"
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
                                value="{{ old('phone') }}"
                                placeholder="Contoh: 081234567890"
                                class="w-full text-sm border-gray-200 rounded-lg focus:ring-primary-500 focus:border-primary-500 @error('phone') border-rose-400 ring-rose-200 @enderror"
                            />
                            <p class="text-xs text-gray-400 mt-1">Hanya angka, 10–15 digit, diawali 0 atau 62. Harus unik jika diisi.</p>
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
                                <option value="buyer" {{ old('role', 'buyer') === 'buyer' ? 'selected' : '' }}>Calon Pembeli (Keluarga Muslim)</option>
                                <option value="agency_partner" {{ old('role') === 'agency_partner' ? 'selected' : '' }}>Mitra Agency Properti</option>
                            </select>
                            <p class="text-xs text-gray-400 mt-1">Akun Super Admin tidak dapat dibuat dari halaman ini.</p>
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
                                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Aktif (Dapat Bertransaksi)</option>
                                <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending (Menunggu Verifikasi)</option>
                                <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspended (Dinonaktifkan)</option>
                            </select>
                            @error('status')
                                <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Grid: Password & Konfirmasi Password --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-gray-100">
                        <div>
                            <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">
                                Kata Sandi <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                required
                                placeholder="Minimal 8 karakter"
                                class="w-full text-sm border-gray-200 rounded-lg focus:ring-primary-500 focus:border-primary-500 @error('password') border-rose-400 ring-rose-200 @enderror"
                            />
                            @error('password')
                                <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1">
                                Konfirmasi Kata Sandi <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="password"
                                name="password_confirmation"
                                id="password_confirmation"
                                required
                                placeholder="Ulangi kata sandi"
                                class="w-full text-sm border-gray-200 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                            />
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
                        <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 text-sm font-semibold text-gray-600 hover:text-gray-800 transition">
                            Batal
                        </a>
                        <button type="submit" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                            Simpan Pengguna Baru
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
