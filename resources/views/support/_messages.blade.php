{{-- Pesan "milik saya" = dari pihak yang sama dengan penonton (sisi admin/sistem, atau pemilik percakapan) --}}
@unless ($viewer->isAdmin())
    {{-- Sapaan otomatis, tidak disimpan di database --}}
    <div class="flex justify-start">
        <div class="max-w-[80%] rounded-2xl px-4 py-2.5 bg-gray-100 text-gray-900 rounded-bl-sm">
            <p class="text-[11px] font-semibold mb-0.5 text-primary-700">Tim Support</p>
            <p class="text-sm whitespace-pre-line break-words">{{ \App\Support\SupportAutoReply::greetingFor($viewer) }}</p>
        </div>
    </div>
@endunless

@foreach ($messages as $message)
    @php($fromSupport = $message->isAutoReply() || $message->sender->isAdmin())
    @php($mine = $fromSupport === $viewer->isAdmin())
    <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
        <div class="max-w-[80%] rounded-2xl px-4 py-2.5 {{ $mine ? 'bg-primary-600 text-white rounded-br-sm' : 'bg-gray-100 text-gray-900 rounded-bl-sm' }} {{ $message->isAutoReply() ? 'opacity-90' : '' }}">
            @if ($fromSupport)
                <p class="text-[11px] font-semibold mb-0.5 {{ $mine ? 'text-primary-100' : 'text-primary-700' }}">
                    @if ($message->isAutoReply())
                        {{ $viewer->isAdmin() ? 'Balasan otomatis' : 'Tim Support · otomatis' }}
                    @else
                        {{ $viewer->isAdmin() ? $message->sender->name : 'Tim Support' }}
                    @endif
                </p>
            @endif
            <p class="text-sm whitespace-pre-line break-words">{{ $message->body }}</p>
            <p class="text-[11px] mt-1 {{ $mine ? 'text-primary-100' : 'text-gray-400' }}">{{ $message->created_at->format('d M Y, H:i') }}</p>
        </div>
    </div>
@endforeach

@if ($viewer->isAdmin() && $messages->isEmpty())
    <p class="text-center text-sm text-gray-400 py-8">Belum ada pesan.</p>
@endif
