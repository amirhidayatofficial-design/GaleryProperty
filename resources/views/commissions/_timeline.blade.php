{{-- Jejak audit perubahan status komisi. $showActor: tampilkan nama admin pelaku (hanya untuk admin). --}}
<ol class="relative border-s border-gray-200 ms-2 space-y-5">
    @foreach ($commission->statusLogs as $log)
        <li class="ms-5">
            <span class="absolute -start-1.5 mt-1.5 w-3 h-3 rounded-full border-2 border-white {{ $loop->first ? 'bg-primary-600' : 'bg-gray-300' }}"></span>
            <div class="flex flex-wrap items-center gap-2">
                <x-admin.badge :value="$log->to_status" :labels="\App\Models\Commission::STATUSES" />
                <time class="text-xs text-gray-400">{{ $log->created_at?->format('d M Y, H:i') }}</time>
            </div>
            @if ($log->note)
                <p class="text-sm text-gray-700 mt-1">{{ $log->note }}</p>
            @endif
            @if ($showActor ?? false)
                <p class="text-xs text-gray-400 mt-0.5">oleh {{ $log->user?->name ?? 'Sistem' }}</p>
            @endif
        </li>
    @endforeach
</ol>
