<x-app-layout>
    <x-slot name="header">
        <x-admin.header
            :title="$conversation->buyer_id === $user->id ? $conversation->agencyPartner->agency_name : $conversation->buyer->name"
            :subtitle="$conversation->propertyProject?->title"
            :index-url="route('chat.index')"
            index-label="Chat"
        />
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-chat-panel
                :messages-url="route('chat.messages', $conversation)"
                :send-url="route('chat.send', $conversation)"
                :max-length="\App\Models\Message::MAX_LENGTH"
            >
                @include('chat._messages')
            </x-chat-panel>
        </div>
    </div>
</x-app-layout>
