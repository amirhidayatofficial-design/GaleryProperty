@if (session('success'))
    <div class="p-4 rounded-xl bg-primary-50 border border-primary-200 text-primary-800 flex items-start gap-3 shadow-sm">
        <svg class="w-5 h-5 text-primary-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="flex-1 text-sm font-medium">{{ session('success') }}</div>
    </div>
@endif

@if (session('error'))
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start gap-3 shadow-sm">
        <svg class="w-5 h-5 text-rose-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="flex-1 text-sm font-medium">{{ session('error') }}</div>
    </div>
@endif
