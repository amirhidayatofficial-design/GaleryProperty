<x-app-layout>
    <x-slot name="header">
        <x-admin.header
            title="Chat"
            :subtitle="$user->isAgencyPartner() ? 'Percakapan dari calon pembeli yang tertarik dengan listing Anda.' : 'Percakapan Anda dengan agency partner.'"
        />
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm divide-y divide-gray-100">
                @forelse ($conversations as $conversation)
                    @php($unread = $conversation->unreadCountFor($user))
                    <a href="{{ route('chat.show', $conversation) }}" class="flex items-start justify-between gap-4 p-5 hover:bg-gray-50/60 transition">
                        <div class="min-w-0">
                            <div class="font-semibold text-gray-900">
                                {{ $user->isAgencyPartner() ? $conversation->buyer->name : $conversation->agencyPartner->agency_name }}
                            </div>
                            <div class="text-xs text-primary-700">{{ $conversation->propertyProject?->title }}</div>
                            <p class="text-sm mt-1 truncate {{ $unread ? 'text-gray-900 font-semibold' : 'text-gray-500' }}">
                                {{ $conversation->latestMessage?->body ?? 'Belum ada pesan.' }}
                            </p>
                        </div>
                        <div class="shrink-0 text-right space-y-1">
                            <div class="text-xs text-gray-400">{{ $conversation->last_message_at?->diffForHumans() }}</div>
                            @if ($unread)
                                <span class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full bg-rose-500 text-white text-[11px] font-bold">{{ $unread }}</span>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="px-6 py-12 text-center">
                        <p class="text-base font-semibold text-gray-600">Belum ada percakapan</p>
                        @if ($user->isBuyer())
                            <p class="text-sm text-gray-400 mt-1">
                                Temukan properti di <a href="{{ route('catalog.index') }}" class="text-primary-700 hover:underline">katalog</a>, lalu klik "Chat Agency".
                            </p>
                        @endif
                    </div>
                @endforelse
            </div>

            {{ $conversations->links() }}
        </div>
    </div>
</x-app-layout>
