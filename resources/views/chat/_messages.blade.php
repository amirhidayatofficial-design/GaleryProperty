@forelse ($messages as $message)
    @php($mine = $message->sender_id === $user->id)
    <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
        <div class="max-w-[80%] rounded-2xl px-4 py-2.5 {{ $mine ? 'bg-primary-600 text-white rounded-br-sm' : 'bg-gray-100 text-gray-900 rounded-bl-sm' }}">
            <p class="text-sm whitespace-pre-line break-words">{{ $message->body }}</p>
            <p class="text-[11px] mt-1 {{ $mine ? 'text-primary-100' : 'text-gray-400' }}">{{ $message->created_at->format('d M Y, H:i') }}</p>
        </div>
    </div>
@empty
    <p class="text-center text-sm text-gray-400 py-8">Belum ada pesan. Mulai percakapan dengan mengirim pesan pertama.</p>
@endforelse
