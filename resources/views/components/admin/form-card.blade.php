@props([
    'action',
    'method' => 'POST',
    'submitLabel' => 'Simpan',
    'cancelUrl',
    'files' => false,
])

<div class="py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <x-admin.flash />

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <form action="{{ $action }}" method="POST" class="space-y-6" data-confirm-leave @if ($files) enctype="multipart/form-data" @endif>
                @csrf
                @if (strtoupper($method) !== 'POST')
                    @method($method)
                @endif

                {{ $slot }}

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <a href="{{ $cancelUrl }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition">Batal</a>
                    <button type="submit" class="inline-flex items-center px-5 py-2 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                        {{ $submitLabel }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
