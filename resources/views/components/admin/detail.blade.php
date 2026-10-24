@props(['label'])

<div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
    <dt class="text-sm font-semibold text-gray-500">{{ $label }}</dt>
    <dd class="mt-1 sm:mt-0 sm:col-span-2 text-sm text-gray-900 break-words">
        @if (trim($slot) === '')
            <span class="text-gray-400 italic">-</span>
        @else
            {{ $slot }}
        @endif
    </dd>
</div>
