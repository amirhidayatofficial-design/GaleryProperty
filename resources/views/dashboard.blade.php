<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    @php
        $roleLabel = match (true) {
            $user->isAdmin() => 'Super Admin',
            $user->isAgencyPartner() => 'Mitra Agency' . ($user->agencyPartner ? ' · ' . $user->agencyPartner->agency_name : ''),
            default => 'Calon Pembeli',
        };
    @endphp

    <section class="bg-primary-900 bg-islamic-pattern-light text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex flex-col md:flex-row md:items-end md:justify-between gap-6">
            <div>
                <p class="text-sm text-gold-300 font-semibold">{{ $roleLabel }}</p>
                <h1 class="font-display text-3xl sm:text-4xl font-bold mt-1">Assalamu'alaikum, {{ $user->name }}</h1>
                <p class="text-primary-100/80 mt-2">Berikut ringkasan yang perlu Anda perhatikan hari ini.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                @foreach ($actions as $action)
                    <a href="{{ $action['url'] }}" class="px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ $loop->first ? 'bg-gold-500 hover:bg-gold-400 text-primary-950' : 'border border-white/30 hover:bg-white/10' }}">
                        {{ $action['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 sm:grid-cols-2 {{ [5 => 'lg:grid-cols-3 xl:grid-cols-5', 4 => 'lg:grid-cols-4'][count($cards)] ?? 'lg:grid-cols-3' }} gap-5">
            @foreach ($cards as $card)
                @php($tag = isset($card['url']) ? 'a' : 'div')
                <{{ $tag }} @isset($card['url']) href="{{ $card['url'] }}" @endisset
                    class="block bg-white rounded-2xl border p-6 transition {{ isset($card['url']) ? 'hover:shadow-md hover:-translate-y-0.5' : '' }} {{ ($card['accent'] ?? false) && $card['value'] ? 'border-gold-300' : 'border-cream-dark' }}">
                    <div class="text-sm text-gray-500">{{ $card['label'] }}</div>
                    <div class="mt-2 font-display text-3xl font-bold {{ ($card['accent'] ?? false) && $card['value'] ? 'text-gold-700' : 'text-primary-900' }}">{{ $card['value'] }}</div>
                    @isset($card['url'])
                        <div class="mt-3 text-xs font-semibold text-primary-700">Lihat →</div>
                    @endisset
                </{{ $tag }}>
            @endforeach
        </div>
    </div>
</x-app-layout>
