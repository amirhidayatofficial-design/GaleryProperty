@props(['light' => false])

{{-- Logo + nama brand. Gunakan :light="true" di atas latar gelap. --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <x-application-logo class="h-9 w-9 shrink-0" />
    <span class="font-display text-xl font-bold tracking-tight {{ $light ? 'text-white' : 'text-primary-900' }}">
        Gallery<span class="{{ $light ? 'text-gold-300' : 'text-gold-600' }}">Property</span>
    </span>
</span>
