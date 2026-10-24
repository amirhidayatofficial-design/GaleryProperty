@props([
    'title',
    'subtitle' => null,
    'indexUrl' => null,
    'indexLabel' => null,
    'createUrl' => null,
    'createLabel' => 'Tambah Data',
])

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        @if ($indexUrl)
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ $indexUrl }}" class="hover:text-primary-600 transition">{{ $indexLabel }}</a>
                <span>/</span>
                <span class="text-gray-800 font-medium">{{ $title }}</span>
            </div>
        @endif
        <h2 class="font-display font-bold text-2xl sm:text-3xl text-primary-950 leading-tight">{{ $title }}</h2>
        @if ($subtitle)
            <p class="text-sm text-gray-500 mt-1">{{ $subtitle }}</p>
        @endif
    </div>
    <div class="flex items-center gap-2">
        {{ $slot }}
        @if ($createUrl)
            <a href="{{ $createUrl }}" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white text-sm font-semibold rounded-lg shadow-sm transition ease-in-out duration-150 gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>{{ $createLabel }}</span>
            </a>
        @elseif ($indexUrl)
            <a href="{{ $indexUrl }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali</span>
            </a>
        @endif
    </div>
</div>
