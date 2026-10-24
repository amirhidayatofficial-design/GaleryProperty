<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>Manajemen Pengguna</span>
                </h2>
                <p class="text-sm text-gray-500 mt-1">Kelola seluruh hak akses, akun mitra agency, dan calon pembeli properti syariah.</p>
            </div>
            <div>
                <a href="{{ route('admin.users.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white text-sm font-semibold rounded-lg shadow-sm transition ease-in-out duration-150 gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Pengguna Baru</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Notification Alerts --}}
            @if (session('success'))
                <div class="p-4 rounded-xl bg-primary-50 border border-primary-200 text-primary-800 flex items-start gap-3 shadow-sm">
                    <svg class="w-5 h-5 text-primary-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1 text-sm font-medium">
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start gap-3 shadow-sm">
                    <svg class="w-5 h-5 text-rose-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1 text-sm font-medium">
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            {{-- KPI Statistics Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4">
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total User</div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
                    <div class="text-xs text-gray-400 mt-1">Terdaftar di sistem</div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                    <div class="text-xs font-semibold text-gold-600 uppercase tracking-wider">Super Admin</div>
                    <div class="mt-2 text-2xl font-bold text-gold-700">{{ $stats['admin'] }}</div>
                    <div class="text-xs text-gray-400 mt-1">Akses penuh</div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                    <div class="text-xs font-semibold text-primary-600 uppercase tracking-wider">Mitra Agency</div>
                    <div class="mt-2 text-2xl font-bold text-primary-700">{{ $stats['agency_partner'] }}</div>
                    <div class="text-xs text-gray-400 mt-1">Kantor keagenan</div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                    <div class="text-xs font-semibold text-sky-600 uppercase tracking-wider">Calon Pembeli</div>
                    <div class="mt-2 text-2xl font-bold text-sky-700">{{ $stats['buyer'] }}</div>
                    <div class="text-xs text-gray-400 mt-1">Keluarga muslim</div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                    <div class="text-xs font-semibold text-teal-600 uppercase tracking-wider">Status Aktif</div>
                    <div class="mt-2 text-2xl font-bold text-teal-700">{{ $stats['active'] }}</div>
                    <div class="text-xs text-primary-600 mt-1">Siap bertransaksi</div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                    <div class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Pending / Suspend</div>
                    <div class="mt-2 text-2xl font-bold text-amber-700">{{ $stats['pending'] + $stats['suspended'] }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ $stats['pending'] }} pend, {{ $stats['suspended'] }} susp</div>
                </div>
            </div>

            {{-- Filter & Search Form --}}
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                    <div class="sm:col-span-5">
                        <label for="search" class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Cari Pengguna</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                type="text"
                                name="search"
                                id="search"
                                value="{{ request('search') }}"
                                placeholder="Ketik nama, email, atau no WhatsApp..."
                                class="w-full pl-9 pr-4 py-2 text-sm border-gray-200 rounded-lg focus:ring-primary-500 focus:border-primary-500 placeholder-gray-400"
                            />
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="role" class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Filter Peran (Role)</label>
                        <select
                            name="role"
                            id="role"
                            class="w-full py-2 px-3 text-sm border-gray-200 rounded-lg focus:ring-primary-500 focus:border-primary-500 text-gray-700"
                        >
                            <option value="">Semua Peran</option>
                            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Super Admin</option>
                            <option value="agency_partner" {{ request('role') === 'agency_partner' ? 'selected' : '' }}>Mitra Agency</option>
                            <option value="buyer" {{ request('role') === 'buyer' ? 'selected' : '' }}>Calon Pembeli</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="status" class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Status Akun</label>
                        <select
                            name="status"
                            id="status"
                            class="w-full py-2 px-3 text-sm border-gray-200 rounded-lg focus:ring-primary-500 focus:border-primary-500 text-gray-700"
                        >
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2 flex gap-2">
                        <button
                            type="submit"
                            class="flex-1 inline-flex justify-center items-center py-2 px-3 bg-gray-900 hover:bg-black text-white text-sm font-semibold rounded-lg shadow-sm transition"
                        >
                            Filter
                        </button>
                        @if (request()->hasAny(['search', 'role', 'status']))
                            <a
                                href="{{ route('admin.users.index') }}"
                                class="inline-flex justify-center items-center py-2 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition"
                                title="Reset Filter"
                            >
                                ✕
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Table Card --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50/75">
                            <tr>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pengguna</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kontak</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Peran (Role)</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Terdaftar</th>
                                <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($users as $user)
                                <tr class="hover:bg-gray-50/60 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm shrink-0
                                                @if($user->role === 'admin') bg-gold-100 text-gold-700
                                                @elseif($user->role === 'agency_partner') bg-primary-100 text-primary-700
                                                @else bg-sky-100 text-sky-700
                                                @endif">
                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="font-semibold text-gray-900 flex items-center gap-2">
                                                    <span>{{ $user->name }}</span>
                                                    @if ($user->id === auth()->id())
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-gold-50 text-gold-700 border border-gold-200">
                                                            Anda
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-gray-400">ID: #{{ $user->id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 font-medium">{{ $user->email }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            @if ($user->phone)
                                                <span class="inline-flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                                    </svg>
                                                    {{ $user->phone }}
                                                </span>
                                            @else
                                                <span class="text-gray-400 italic">Belum ada WhatsApp</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($user->role === 'admin')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gold-50 text-gold-700 border border-gold-200">
                                                Super Admin
                                            </span>
                                        @elseif ($user->role === 'agency_partner')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-primary-50 text-primary-700 border border-primary-200">
                                                Mitra Agency
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                                                Calon Pembeli
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($user->status === 'active')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-primary-50 text-primary-700 border border-primary-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                                                Aktif
                                            </span>
                                        @elseif ($user->status === 'pending')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                Pending
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                Suspended
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $user->created_at ? $user->created_at->format('d M Y, H:i') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="inline-flex items-center gap-2">
                                            {{-- Detail Link --}}
                                            <a href="{{ route('admin.users.show', $user) }}" class="p-1.5 text-gray-500 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition" title="Lihat Profil">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>

                                            {{-- Edit Link --}}
                                            <a href="{{ route('admin.users.edit', $user) }}" class="p-1.5 text-gray-500 hover:text-gold-600 hover:bg-gold-50 rounded-lg transition" title="Ubah Data">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>

                                            {{-- Delete Button --}}
                                            @if ($user->id !== auth()->id())
                                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna {{ addslashes($user->name) }}? Tindakan ini tidak dapat dibatalkan.');" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 text-gray-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus Pengguna">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="p-1.5 text-gray-300 cursor-not-allowed" title="Anda tidak dapat menghapus akun sendiri">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <p class="text-base font-semibold text-gray-600">Tidak ada pengguna yang cocok dengan kriteria filter</p>
                                        <p class="text-sm text-gray-400 mt-1">Coba ubah kata kunci pencarian atau reset filter.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($users->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
