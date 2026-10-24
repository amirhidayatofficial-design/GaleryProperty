@props([
    'action',
    'placeholder' => 'Ketik kata kunci...',
    'keys' => ['search'],
])

<div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
    <form method="GET" action="{{ $action }}" class="flex flex-col sm:flex-row gap-3 sm:items-end">
        <div class="flex-1">
            <label for="search" class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Cari</label>
            <input
                type="text"
                name="search"
                id="search"
                value="{{ request('search') }}"
                placeholder="{{ $placeholder }}"
                class="w-full px-4 py-2 text-sm border-gray-200 rounded-lg focus:ring-primary-500 focus:border-primary-500 placeholder-gray-400"
            />
        </div>

        {{ $slot }}

        <div class="flex gap-2">
            <button type="submit" class="inline-flex justify-center items-center py-2 px-4 bg-gray-900 hover:bg-black text-white text-sm font-semibold rounded-lg shadow-sm transition">
                Filter
            </button>
            @if (request()->hasAny($keys))
                <a href="{{ $action }}" class="inline-flex justify-center items-center py-2 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition" title="Reset Filter">✕</a>
            @endif
        </div>
    </form>
</div>
