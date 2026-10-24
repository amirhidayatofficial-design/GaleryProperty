@props([
    'headers' => [],
    'paginator' => null,
])

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50/75">
                <tr>
                    @foreach ($headers as $header)
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $header }}</th>
                    @endforeach
                    <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @if ($paginator && $paginator->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $paginator->links() }}
        </div>
    @endif
</div>
