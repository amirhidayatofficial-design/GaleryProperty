<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                    <a href="{{ route('admin.users.index') }}" class="hover:text-primary-600 transition">Manajemen Pengguna</a>
                    <span>/</span>
                    <span class="text-gray-800 font-medium">Detail Pengguna</span>
                </div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <span>{{ $user->name }}</span>
                    @if ($user->id === auth()->id())
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-gold-50 text-gold-700 border border-gold-200">
                            Akun Anda
                        </span>
                    @endif
                </h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali</span>
                </a>
                <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg shadow-sm transition gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Ubah Profil</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Profile Card --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 pb-6 border-b border-gray-100">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl flex items-center justify-center font-bold text-xl
                            @if($user->role === 'admin') bg-gold-100 text-gold-700
                            @elseif($user->role === 'agency_partner') bg-primary-100 text-primary-700
                            @else bg-sky-100 text-sky-700
                            @endif">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ $user->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $user->email }}</p>
                            <div class="flex items-center gap-2 mt-2">
                                {{-- Role Badge --}}
                                @if ($user->role === 'admin')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gold-50 text-gold-700 border border-gold-200">
                                        Super Admin
                                    </span>
                                @elseif ($user->role === 'agency_partner')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary-50 text-primary-700 border border-primary-200">
                                        Mitra Agency
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                                        Calon Pembeli
                                    </span>
                                @endif

                                {{-- Status Badge --}}
                                @if ($user->status === 'active')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary-50 text-primary-700 border border-primary-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                                        Aktif
                                    </span>
                                @elseif ($user->status === 'pending')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Pending
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Suspended
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($user->id !== auth()->id())
                        <div>
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun {{ addslashes($user->name) }} secara permanen?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-3.5 py-2 text-rose-700 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 rounded-lg text-xs font-semibold transition gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    <span>Hapus Pengguna</span>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                {{-- Detail Properties List --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-6">
                    <div>
                        <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Nomor WhatsApp / Telepon</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900 flex items-center gap-2">
                            @if ($user->phone)
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <span>{{ $user->phone }}</span>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $user->phone) }}" target="_blank" class="text-xs text-primary-600 hover:underline">
                                    (Buka WhatsApp)
                                </a>
                            @else
                                <span class="text-gray-400 italic">Belum didaftarkan</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Verifikasi Email</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900">
                            @if ($user->email_verified_at)
                                <span class="inline-flex items-center gap-1 text-primary-700">
                                    <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Terverifikasi ({{ $user->email_verified_at->format('d M Y, H:i') }})
                                </span>
                            @else
                                <span class="text-amber-600">Belum diverifikasi</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal Registrasi Akun</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900">
                            {{ $user->created_at ? $user->created_at->format('d F Y, H:i:s') : '-' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Terakhir Diperbarui</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900">
                            {{ $user->updated_at ? $user->updated_at->format('d F Y, H:i:s') : '-' }}
                        </dd>
                    </div>
                </div>
            </div>

            {{-- Relasi: Agency Partner Detail (jika ada) --}}
            @if ($user->agencyPartner)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                    <h4 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span>Profil Entitas Mitra Agency Terhubung</span>
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div class="bg-gray-50 p-3.5 rounded-xl">
                            <div class="text-xs text-gray-500">Nama Kantor Agency</div>
                            <div class="font-bold text-gray-900 mt-0.5">{{ $user->agencyPartner->agency_name }}</div>
                        </div>
                        <div class="bg-gray-50 p-3.5 rounded-xl">
                            <div class="text-xs text-gray-500">Nomor Izin Usaha / SIU</div>
                            <div class="font-medium text-gray-900 mt-0.5">{{ $user->agencyPartner->license_number ?? '-' }}</div>
                        </div>
                        <div class="bg-gray-50 p-3.5 rounded-xl">
                            <div class="text-xs text-gray-500">Rekening Bank Syariah</div>
                            <div class="font-medium text-gray-900 mt-0.5">
                                {{ $user->agencyPartner->bank_name ?? '-' }} ({{ $user->agencyPartner->bank_account_number ?? '-' }})
                            </div>
                            <div class="text-xs text-gray-500 mt-0.5">a.n. {{ $user->agencyPartner->bank_account_holder ?? '-' }}</div>
                        </div>
                        <div class="bg-gray-50 p-3.5 rounded-xl">
                            <div class="text-xs text-gray-500">Status Legalitas Agency</div>
                            <div class="font-bold text-primary-700 mt-0.5 uppercase">{{ $user->agencyPartner->verification_status }}</div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Relasi: Buyer Leads (jika ada) --}}
            @if ($user->buyerLeads && $user->buyerLeads->isNotEmpty())
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                    <h4 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span>Minat Properti / Buyer Profiling Terhubung ({{ $user->buyerLeads->count() }})</span>
                    </h4>

                    <div class="space-y-3">
                        @foreach ($user->buyerLeads as $lead)
                            <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-sm">
                                <div>
                                    <div class="font-bold text-gray-900">{{ $lead->full_name }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        Preferensi: {{ $lead->preferred_location ?? 'Lokasi Terbuka' }} |
                                        Skema: {{ strtoupper($lead->preferred_scheme ?? 'Syariah') }}
                                    </div>
                                    <div class="text-xs text-primary-700 font-semibold mt-1">
                                        Anggaran: Rp {{ number_format($lead->budget_min, 0, ',', '.') }} - Rp {{ number_format($lead->budget_max, 0, ',', '.') }}
                                    </div>
                                </div>
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 border border-sky-200 uppercase">
                                        {{ str_replace('_', ' ', $lead->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
