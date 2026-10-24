@props([
    'messagesUrl' => null,
    'sendUrl',
    'maxLength' => 1000,
    'placeholder' => 'Tulis pesan...',
])

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col h-[70vh]">
    {{-- Daftar pesan diperbarui otomatis setiap 5 detik tanpa reload halaman --}}
    <div
        x-data="{
            refresh() {
                fetch(@js($messagesUrl), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(response => response.ok ? response.text() : null)
                    .then(html => {
                        if (html === null || html === this.$el.innerHTML) return;
                        const atBottom = this.$el.scrollHeight - this.$el.scrollTop - this.$el.clientHeight < 40;
                        this.$el.innerHTML = html;
                        if (atBottom) this.$el.scrollTop = this.$el.scrollHeight;
                    });
            }
        }"
        x-init="$el.scrollTop = $el.scrollHeight; @if ($messagesUrl) setInterval(() => refresh(), 5000) @endif"
        class="flex-1 overflow-y-auto p-6 space-y-3"
    >
        {{ $slot }}
    </div>

    <form method="POST" action="{{ $sendUrl }}" class="border-t border-gray-100 p-4" data-confirm-leave>
        @csrf
        <div class="flex items-end gap-3">
            <textarea
                name="body"
                rows="2"
                maxlength="{{ $maxLength }}"
                required
                placeholder="{{ $placeholder }}"
                {{-- Enter mengirim pesan, Shift+Enter membuat baris baru. Abaikan saat IME sedang menyusun teks. --}}
                x-data
                @keydown.enter="if ($event.shiftKey || $event.isComposing) return; $event.preventDefault(); if ($el.value.trim() !== '') $el.form.requestSubmit()"
                class="flex-1 text-sm border-gray-200 rounded-lg focus:ring-primary-500 focus:border-primary-500 resize-none"
            >{{ old('body') }}</textarea>
            <button type="submit" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg transition">Kirim</button>
        </div>
        @error('body')
            <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
        @enderror
    </form>
</div>
