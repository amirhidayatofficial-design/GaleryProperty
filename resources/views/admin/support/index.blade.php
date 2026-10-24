<x-app-layout>
    <x-slot name="header">
        <x-admin.header
            title="Customer Support"
            subtitle="Pertanyaan dan kendala dari calon pembeli serta mitra agency."
        />
    </x-slot>

    @php($roles = ['' => 'Semua', 'buyer' => 'Calon Pembeli', 'agency_partner' => 'Mitra Agency'])

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <div class="flex flex-wrap gap-2">
                @foreach ($roles as $value => $label)
                    <a href="{{ route('admin.support.index', array_filter(['role' => $value])) }}"
                       class="px-3 py-1.5 rounded-full text-sm font-medium transition {{ request('role', '') === $value ? 'bg-primary-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:border-primary-300' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm divide-y divide-gray-100">
                @forelse ($conversations as $conversation)
                    @php($unread = $conversation->unreadCountFor($viewer))
                    @php($latest = $conversation->latestMessage)
                    <a href="{{ route('admin.support.show', $conversation) }}" class="flex items-start justify-between gap-4 p-5 hover:bg-gray-50/60 transition">
                        <div class="min-w-0">
                            <div class="font-semibold text-gray-900">{{ $conversation->user->name }}</div>
                            <div class="text-xs text-primary-700">{{ $conversation->user->isAgencyPartner() ? 'Mitra Agency' : 'Calon Pembeli' }}</div>
                            <p class="text-sm mt-1 truncate {{ $unread ? 'text-gray-900 font-semibold' : 'text-gray-500' }}">
                                @if ($latest?->isAutoReply())
                                    <span class="text-gray-400 font-normal">Balasan otomatis:</span>
                                @elseif ($latest?->sender->isAdmin())
                                    <span class="text-gray-400 font-normal">Anda:</span>
                                @endif
                                {{ $latest?->body }}
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
                        <p class="text-base font-semibold text-gray-600">Belum ada pesan bantuan</p>
                        <p class="text-sm text-gray-400 mt-1">Pesan dari calon pembeli dan mitra agency melalui menu Bantuan akan muncul di sini.</p>
                    </div>
                @endforelse
            </div>

            {{ $conversations->links() }}
        </div>
    </div>
</x-app-layout>
