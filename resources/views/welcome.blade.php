<x-app-layout>
    <x-slot name="title">Properti Syariah untuk Keluarga Muslim</x-slot>

    {{-- ============ HERO ============ --}}
    <section class="relative overflow-hidden bg-primary-900 bg-islamic-pattern-light text-white">
        <div class="absolute inset-0 bg-gradient-to-br from-primary-950/60 via-transparent to-primary-700/40 pointer-events-none"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28 grid lg:grid-cols-2 gap-12 items-center">
            <div class="space-y-8">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-gold-400/40 bg-gold-400/10 text-gold-200 text-xs font-semibold tracking-wider uppercase">
                    <span class="w-1.5 h-1.5 rounded-full bg-gold-300"></span>
                    100% Akad Murni Syariah
                </span>

                <h1 class="font-display text-4xl sm:text-5xl lg:text-6xl font-bold leading-[1.1]">
                    Hunian halal untuk keluarga,
                    <span class="text-gold-300">akad yang menenangkan.</span>
                </h1>

                <p class="text-lg text-primary-100/90 max-w-xl leading-relaxed">
                    Temukan rumah, kavling, dan ruko syariah yang sudah dikurasi legalitas dan kesesuaian akadnya.
                    Tanpa riba, tanpa denda keterlambatan, tanpa sita paksa.
                </p>

                {{-- Pencarian cepat ke katalog --}}
                <form method="GET" action="{{ route('catalog.index') }}" class="flex flex-col sm:flex-row gap-3 p-2 bg-white rounded-2xl shadow-xl max-w-xl">
                    <label for="hero-search" class="sr-only">Cari lokasi atau nama projek</label>
                    <input
                        id="hero-search"
                        type="text"
                        name="search"
                        placeholder="Cari kota atau nama projek, mis. Bogor"
                        class="flex-1 border-0 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-primary-500"
                    />
                    <button type="submit" class="px-6 py-3 rounded-xl bg-gold-500 hover:bg-gold-400 text-primary-950 font-semibold transition">
                        Cari Properti
                    </button>
                </form>

                <dl class="grid grid-cols-3 gap-6 max-w-md pt-2">
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-primary-200/70">Projek tayang</dt>
                        <dd class="font-display text-3xl font-bold text-gold-300">{{ $stats['projects'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-primary-200/70">Agency terverifikasi</dt>
                        <dd class="font-display text-3xl font-bold text-gold-300">{{ $stats['agencies'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-primary-200/70">Kota</dt>
                        <dd class="font-display text-3xl font-bold text-gold-300">{{ $stats['cities'] }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Ilustrasi: bingkai lengkung mihrab berisi kartu jaminan syariah --}}
            <div class="hidden lg:flex justify-center">
                <div class="relative w-[420px] h-[500px]">
                    <div class="absolute inset-0 rounded-t-full border-2 border-gold-400/60"></div>
                    <div class="absolute inset-4 rounded-t-full bg-primary-800/80 border border-gold-400/20 overflow-hidden flex items-end justify-center">
                        <x-application-logo class="absolute top-16 left-1/2 -translate-x-1/2 w-28 h-28 opacity-90" />
                        <div class="relative m-8 w-full bg-white rounded-2xl p-6 shadow-2xl text-gray-900 space-y-4">
                            <div class="text-xs font-semibold uppercase tracking-wider text-gold-700">Jaminan setiap listing</div>
                            <ul class="space-y-3 text-sm">
                                @foreach (['Legalitas & dokumen kuasa developer diverifikasi', 'Akad digital ditandatangani 3 pihak', 'Komisi agen transparan & tercatat'] as $point)
                                    <li class="flex items-start gap-3">
                                        <span class="mt-0.5 w-5 h-5 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center shrink-0">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        </span>
                                        <span>{{ $point }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============ PRINSIP SYARIAH ============ --}}
    <section class="bg-white border-b border-cream-dark">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ([
                ['Tanpa Riba', 'Harga disepakati di awal, tidak berubah mengikuti bunga.'],
                ['Tanpa Denda', 'Tidak ada denda atas keterlambatan pembayaran.'],
                ['Tanpa Sita Paksa', 'Masalah diselesaikan secara musyawarah, bukan sita sepihak.'],
                ['Tanpa Asuransi Non-Halal', 'Tidak mewajibkan produk asuransi berbasis riba.'],
            ] as [$label, $desc])
                <div class="flex items-start gap-3">
                    <span class="w-10 h-10 shrink-0 rounded-full bg-gold-100 text-primary-700 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    </span>
                    <div>
                        <div class="font-semibold text-primary-900">{{ $label }}</div>
                        <p class="text-sm text-gray-500 mt-0.5">{{ $desc }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ============ LISTING TERBARU ============ --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-10">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-gold-700">Listing Terbaru</p>
                <h2 class="font-display text-3xl sm:text-4xl font-bold text-primary-950 mt-2">Properti pilihan yang sudah dikurasi</h2>
            </div>
            <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-primary-700 hover:text-primary-900">
                Lihat semua katalog →
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($featuredProjects as $project)
                <x-property-card :project="$project" />
            @empty
                <div class="col-span-full bg-white rounded-2xl border border-cream-dark px-6 py-12 text-center text-gray-500">
                    Belum ada listing yang tayang. Segera hadir, insyaAllah.
                </div>
            @endforelse
        </div>
    </section>

    {{-- ============ CARA KERJA ============ --}}
    <section id="cara-kerja" class="bg-white border-y border-cream-dark scroll-mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center max-w-2xl mx-auto mb-14">
                <p class="text-sm font-semibold uppercase tracking-wider text-gold-700">Cara Kerja</p>
                <h2 class="font-display text-3xl sm:text-4xl font-bold text-primary-950 mt-2">Dari mencari sampai akad, semuanya tercatat</h2>
            </div>

            <ol class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                @foreach ([
                    ['Temukan properti', 'Jelajahi katalog yang hanya berisi listing terverifikasi admin, lalu saring sesuai lokasi, harga, dan skema.'],
                    ['Chat dengan agency', 'Tanyakan detail langsung lewat chat di website. Riwayat percakapan tersimpan rapi.'],
                    ['Survei & booking', 'Agency mendampingi survei lokasi dan proses booking unit pilihan keluarga Anda.'],
                    ['Akad syariah digital', 'Akad ditandatangani pembeli, agency, dan developer secara digital dengan nomor resmi.'],
                ] as $i => [$step, $desc])
                    <li>
                        <div class="w-12 h-14 rounded-t-full rounded-b-lg bg-primary-700 text-gold-300 font-display text-xl font-bold flex items-end justify-center pb-2 shadow-sm">
                            {{ $i + 1 }}
                        </div>
                        <h3 class="mt-5 font-semibold text-lg text-primary-950">{{ $step }}</h3>
                        <p class="mt-2 text-sm text-gray-500 leading-relaxed">{{ $desc }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- ============ SKEMA SYARIAH ============ --}}
    <section id="skema-syariah" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 scroll-mt-20">
        <div class="grid lg:grid-cols-3 gap-12">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-gold-700">Skema Pembayaran</p>
                <h2 class="font-display text-3xl sm:text-4xl font-bold text-primary-950 mt-2">Pilih akad yang sesuai kemampuan keluarga</h2>
                <p class="mt-4 text-gray-500 leading-relaxed">
                    Setiap listing mencantumkan skema syariah yang digunakan, sehingga Anda tahu sejak awal bagaimana transaksi akan berjalan.
                </p>
            </div>

            <div class="lg:col-span-2 grid sm:grid-cols-2 gap-5">
                @foreach ([
                    'Istishna' => 'Akad pesan bangun: harga dan spesifikasi disepakati di awal, pembayaran bertahap mengikuti progres pembangunan.',
                    'Murabahah' => 'Jual beli dengan margin keuntungan yang disepakati di awal. Cicilan tetap hingga lunas, tanpa bunga mengambang.',
                    'Cash Bertahap' => 'Pembayaran diangsur langsung ke developer dalam jangka waktu tertentu, tanpa melibatkan bank.',
                    'Cash Keras' => 'Pembayaran lunas sekaligus, biasanya dengan harga terbaik dari developer.',
                ] as $scheme => $desc)
                    <div class="bg-white rounded-2xl border border-cream-dark p-6 hover:border-gold-300 transition">
                        <div class="flex items-center gap-3">
                            <span class="w-1.5 h-7 rounded-full bg-gold-400"></span>
                            <h3 class="font-display text-xl font-bold text-primary-900">{{ $scheme }}</h3>
                        </div>
                        <p class="mt-3 text-sm text-gray-600 leading-relaxed">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============ AJAKAN ============ --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
        <div class="grid md:grid-cols-2 gap-6">
            <div class="rounded-3xl bg-primary-800 bg-islamic-pattern-light p-8 sm:p-10 text-white">
                <p class="text-sm font-semibold uppercase tracking-wider text-gold-300">Untuk Keluarga Muslim</p>
                <h3 class="font-display text-2xl sm:text-3xl font-bold mt-3">Mulai cari rumah impian yang berkah</h3>
                <p class="mt-3 text-primary-100/80">Daftar gratis untuk chat dengan agency dan menyimpan riwayat percakapan Anda.</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    @guest
                        <a href="{{ route('register') }}" class="px-5 py-3 rounded-xl bg-gold-500 hover:bg-gold-400 text-primary-950 font-semibold transition">Daftar Sekarang</a>
                    @endguest
                    <a href="{{ route('catalog.index') }}" class="px-5 py-3 rounded-xl border border-white/30 hover:bg-white/10 font-semibold transition">Lihat Katalog</a>
                </div>
            </div>

            <div class="rounded-3xl bg-gold-50 border border-gold-200 p-8 sm:p-10">
                <p class="text-sm font-semibold uppercase tracking-wider text-gold-700">Untuk Mitra Agency</p>
                <h3 class="font-display text-2xl sm:text-3xl font-bold text-primary-950 mt-3">Pasarkan properti syariah bersama kami</h3>
                <ul class="mt-5 space-y-2 text-sm text-primary-900">
                    <li class="flex gap-2"><span class="text-gold-600">✦</span> Ajukan listing dari developer yang Anda wakili</li>
                    <li class="flex gap-2"><span class="text-gold-600">✦</span> Terima leads calon pembeli langsung lewat chat</li>
                    <li class="flex gap-2"><span class="text-gold-600">✦</span> Komisi dihitung otomatis dan transparan</li>
                </ul>
                <p class="mt-8 text-sm text-gray-600">Hubungi admin GalleryProperty untuk mendaftarkan kantor agency Anda.</p>
            </div>
        </div>
    </section>
</x-app-layout>
