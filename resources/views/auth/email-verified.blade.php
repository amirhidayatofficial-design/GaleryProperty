<x-guest-layout>
    <x-slot name="title">Email Terverifikasi</x-slot>

    <div class="text-center space-y-5">
        <div class="mx-auto w-16 h-16 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center">
            <svg class="w-9 h-9" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <div>
            <h1 class="font-display text-2xl font-bold text-primary-950">Alhamdulillah, email berhasil diverifikasi</h1>
            @if ($user->isSuspended())
                <p class="text-sm text-gray-600 mt-2">
                    Email <strong>{{ $user->email }}</strong> sudah terverifikasi, tetapi akun ini sedang ditangguhkan admin. Silakan hubungi admin GalleryProperty.
                </p>
            @else
                <p class="text-sm text-gray-600 mt-2">
                    Akun <strong>{{ $user->email }}</strong> sudah aktif dan siap digunakan.
                </p>
            @endif
        </div>

        @unless ($user->isSuspended())
            @auth
                <a href="{{ route('dashboard') }}" class="block w-full px-5 py-3.5 rounded-xl bg-primary-700 hover:bg-primary-800 text-white text-base font-semibold transition">
                    Lanjut ke Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="block w-full px-5 py-3.5 rounded-xl bg-primary-700 hover:bg-primary-800 text-white text-base font-semibold transition">
                    Masuk ke Akun Saya
                </a>
            @endauth
        @endunless
    </div>
</x-guest-layout>
