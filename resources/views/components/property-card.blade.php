@props(['project'])

@php
    $photo = $project->primaryPhoto;
    $hasPhoto = $photo && Storage::disk('public')->exists($photo->file_path);
@endphp

<a href="{{ route('catalog.show', $project) }}" {{ $attributes->merge(['class' => 'group flex flex-col bg-white rounded-2xl border border-cream-dark shadow-sm overflow-hidden hover:shadow-lg hover:-translate-y-0.5 transition duration-200']) }}>
    <div class="relative h-48">
        @if ($hasPhoto)
            <img src="{{ Storage::url($photo->file_path) }}" alt="{{ $project->title }}" class="h-full w-full object-cover" loading="lazy" />
        @else
            {{-- Placeholder foto: ornamen geometris dengan ikon tipe properti --}}
            <div class="h-full w-full bg-primary-800 bg-islamic-pattern-light flex flex-col items-center justify-center gap-2 text-primary-100">
                <svg class="w-10 h-10 text-gold-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    @if ($project->property_type === 'tanah_kavling')
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z" />
                    @elseif ($project->property_type === 'ruko')
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z" />
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    @endif
                </svg>
                <span class="text-xs font-semibold uppercase tracking-wider">{{ \App\Models\PropertyProject::PROPERTY_TYPES[$project->property_type] ?? 'Properti' }}</span>
            </div>
        @endif

        <span class="absolute top-3 left-3 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gold-400 text-primary-950 shadow-sm">
            {{ \App\Models\PropertyProject::SYARIAH_SCHEMES[$project->syariah_scheme] ?? $project->syariah_scheme }}
        </span>
        @if ($project->status !== 'available')
            <span class="absolute top-3 right-3 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-white/90 text-gray-700">
                {{ \App\Models\PropertyProject::STATUSES[$project->status] ?? $project->status }}
            </span>
        @endif
    </div>

    <div class="flex-1 flex flex-col p-5">
        <h3 class="font-display text-lg font-bold text-primary-950 group-hover:text-primary-700 transition leading-snug">{{ $project->title }}</h3>
        <p class="mt-1 text-sm text-gray-500 flex items-center gap-1">
            <svg class="w-4 h-4 text-gold-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            {{ $project->city }}, {{ $project->province }}
        </p>

        @if ($project->bedroom || $project->land_area)
            <p class="mt-3 text-xs text-gray-500 flex flex-wrap gap-x-3 gap-y-1">
                @if ($project->bedroom) <span>{{ $project->bedroom }} KT</span> @endif
                @if ($project->bathroom) <span>{{ $project->bathroom }} KM</span> @endif
                @if ($project->building_area) <span>LB {{ $project->building_area }} m²</span> @endif
                @if ($project->land_area) <span>LT {{ $project->land_area }} m²</span> @endif
            </p>
        @endif

        <div class="flex-1 min-h-4"></div>

        <div class="pt-4 flex items-end justify-between border-t border-cream-dark">
            <div>
                <div class="text-[11px] uppercase tracking-wider text-gray-400">Harga</div>
                <div class="text-lg font-bold text-primary-700">Rp {{ number_format($project->price, 0, ',', '.') }}</div>
            </div>
            <span class="text-sm font-semibold text-gold-700 group-hover:translate-x-0.5 transition">Detail →</span>
        </div>
    </div>
</a>
