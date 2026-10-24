<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.partials.head')
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen grid lg:grid-cols-2">
            {{-- Panel brand (desktop) --}}
            <div class="hidden lg:flex flex-col justify-between bg-primary-900 bg-islamic-pattern-light p-12 text-white">
                <a href="{{ url('/') }}"><x-brand :light="true" /></a>

                <div class="space-y-6 max-w-md">
                    <p class="font-display text-4xl leading-tight">
                        Hunian halal, <span class="text-gold-300">akad yang menenangkan.</span>
                    </p>
                    <p class="text-primary-100/80 leading-relaxed">
                        Temukan properti syariah terkurasi tanpa riba, tanpa denda keterlambatan, dan tanpa sita paksa sepihak.
                    </p>
                </div>

                <p class="text-xs text-primary-200/60">&copy; {{ date('Y') }} GalleryProperty</p>
            </div>

            {{-- Form --}}
            <div class="flex flex-col items-center justify-center bg-cream px-6 py-12">
                <a href="{{ url('/') }}" class="lg:hidden mb-8"><x-brand /></a>

                <div class="w-full sm:max-w-md bg-white border border-cream-dark shadow-sm rounded-2xl px-6 py-8 sm:px-8">
                    {{ $slot }}
                </div>

                <a href="{{ route('catalog.index') }}" class="mt-6 text-sm text-gray-500 hover:text-primary-700 transition">← Lihat katalog properti</a>
            </div>
        </div>
    </body>
</html>
