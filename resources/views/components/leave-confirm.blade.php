{{-- Modal konfirmasi saat meninggalkan form yang belum disimpan. Logika ada di resources/js/leave-guard.js --}}
<div
    x-data="leaveGuard"
    x-show="open"
    x-on:keydown.escape.window="stay()"
    class="fixed inset-0 z-50 flex items-center justify-center px-4"
    style="display: none;"
    role="dialog"
    aria-modal="true"
    aria-labelledby="leave-confirm-title"
>
    <div x-show="open" x-transition.opacity class="absolute inset-0 bg-gray-500/75" x-on:click="stay()"></div>

    <div
        x-show="open"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        class="relative w-full max-w-md bg-white rounded-2xl shadow-xl p-6"
    >
        <h2 id="leave-confirm-title" class="text-lg font-bold text-gray-900">Tinggalkan halaman ini?</h2>
        <p class="mt-2 text-sm text-gray-600">Data yang sudah Anda isi belum disimpan dan akan hilang jika Anda meninggalkan halaman ini.</p>

        <div class="mt-6 flex justify-end gap-3">
            <button type="button" x-on:click="leave()" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition">
                Tinggalkan
            </button>
            <button type="button" x-on:click="stay()" x-effect="open && $nextTick(() => $el.focus())" class="inline-flex items-center px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                Lanjut Mengisi
            </button>
        </div>
    </div>
</div>
