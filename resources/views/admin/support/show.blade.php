<x-app-layout>
    <x-slot name="header">
        <x-admin.header
            :title="$conversation->user->name"
            :subtitle="($conversation->user->isAgencyPartner() ? 'Mitra Agency' : 'Calon Pembeli') . ' · ' . $conversation->user->email"
            :index-url="route('admin.support.index')"
            index-label="Customer Support"
        />
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-chat-panel
                :messages-url="route('admin.support.messages', $conversation)"
                :send-url="route('admin.support.send', $conversation)"
                :max-length="\App\Http\Controllers\SupportController::MAX_LENGTH"
                placeholder="Tulis balasan..."
            >
                @include('support._messages')
            </x-chat-panel>
        </div>
    </div>
</x-app-layout>
