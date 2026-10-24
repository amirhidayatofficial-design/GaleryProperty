<footer class="bg-primary-950 bg-islamic-pattern-light text-primary-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid grid-cols-1 md:grid-cols-4 gap-10">
        <div class="md:col-span-2 space-y-4">
            <x-brand :light="true" />
            <p class="text-sm text-primary-200/80 max-w-sm leading-relaxed">
                Wadah terpercaya untuk mencari, menyaring, dan bertransaksi properti halal bagi keluarga muslim, pengembang, dan mitra agency.
            </p>
        </div>

        <div>
            <h3 class="text-sm font-semibold text-gold-300 uppercase tracking-wider mb-4">Jelajahi</h3>
            <ul class="space-y-2 text-sm">
                @unless (auth()->user()?->isAdmin())
                    <li><a href="{{ route('catalog.index') }}" class="hover:text-white transition">Katalog Properti</a></li>
                @endunless
                @guest
                    {{-- Landing page hanya bisa diakses pengunjung yang belum login --}}
                    <li><a href="{{ url('/#cara-kerja') }}" class="hover:text-white transition">Cara Kerja</a></li>
                    <li><a href="{{ url('/#skema-syariah') }}" class="hover:text-white transition">Skema Syariah</a></li>
                @endguest
            </ul>
        </div>

        <div>
            <h3 class="text-sm font-semibold text-gold-300 uppercase tracking-wider mb-4">Akun</h3>
            <ul class="space-y-2 text-sm">
                @auth
                    <li><a href="{{ route('dashboard') }}" class="hover:text-white transition">Dashboard</a></li>
                    <li><a href="{{ route('profile.edit') }}" class="hover:text-white transition">Profil</a></li>
                @else
                    <li><a href="{{ route('login') }}" class="hover:text-white transition">Masuk</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-white transition">Daftar sebagai Pembeli</a></li>
                @endauth
            </ul>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row gap-2 sm:items-center sm:justify-between text-xs text-primary-200/70">
            <p>&copy; {{ date('Y') }} GalleryProperty. Dikembangkan untuk kemaslahatan transaksi properti tanpa riba.</p>
            <p>Tanpa riba · Tanpa denda · Tanpa sita paksa</p>
        </div>
    </div>
</footer>
