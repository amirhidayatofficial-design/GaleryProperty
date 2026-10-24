<x-app-layout>
    <x-slot name="header">
        <x-admin.header
            title="Bantuan"
            subtitle="Hubungi tim admin GalleryProperty untuk pertanyaan seputar akun, listing, transaksi, atau kendala lain."
        />
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-chat-panel
                :messages-url="route('support.messages')"
                :send-url="route('support.send')"
                :max-length="\App\Http\Controllers\SupportController::MAX_LENGTH"
                placeholder="Tulis pertanyaan Anda..."
            >
                @include('support._messages', ['viewer' => $user])
            </x-chat-panel>
        </div>
    </div>
</x-app-layout>
