<x-guest-layout>
    <x-slot name="title">Verifikasi Email</x-slot>

    <div class="space-y-6">
        <div class="text-center space-y-3">
            <div class="mx-auto w-16 h-16 rounded-full bg-gold-100 text-gold-700 flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <h1 class="font-display text-2xl font-bold text-primary-950">Cek email Anda</h1>
            <p class="text-sm text-gray-600">Akun Anda belum aktif. Kami sudah mengirim tautan verifikasi ke:</p>
            <p class="text-base font-semibold text-primary-900 break-all">{{ auth()->user()->email }}</p>
        </div>

        <ol class="space-y-3 text-sm text-gray-700">
            <li class="flex gap-3"><span class="w-6 h-6 shrink-0 rounded-full bg-primary-700 text-white text-xs font-bold flex items-center justify-center">1</span> Buka aplikasi email (Gmail, dll.) di HP atau komputer.</li>
            <li class="flex gap-3"><span class="w-6 h-6 shrink-0 rounded-full bg-primary-700 text-white text-xs font-bold flex items-center justify-center">2</span> Cari email dari GalleryProperty. Jika tidak ada, cek folder <strong>Spam</strong> atau <strong>Promosi</strong>.</li>
            <li class="flex gap-3"><span class="w-6 h-6 shrink-0 rounded-full bg-primary-700 text-white text-xs font-bold flex items-center justify-center">3</span> Tekan tombol <strong>Verifikasi Alamat Email</strong> di dalam email. Bisa dibuka di HP mana saja, tanpa perlu masuk ulang.</li>
        </ol>

        @if (session('status') == 'verification-link-sent')
            <div class="p-3 rounded-xl bg-primary-50 border border-primary-200 text-sm font-medium text-primary-800">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </div>
        @endif

        @if (session('error'))
            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-sm font-medium text-rose-800">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="w-full px-5 py-3.5 rounded-xl bg-primary-700 hover:bg-primary-800 text-white text-base font-semibold transition">
                Kirim Ulang Email Verifikasi
            </button>
        </form>

        <div class="flex items-center justify-between text-sm pt-2 border-t border-cream-dark">
            <a href="{{ route('profile.edit') }}" class="text-gray-600 hover:text-primary-800">Email salah? Ubah di profil</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-gray-600 hover:text-primary-800">{{ __('Log Out') }}</button>
            </form>
        </div>
    </div>
</x-guest-layout>
