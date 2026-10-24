@props([
    'name',
    'label',
    'checked' => false,
    'hint' => null,
])

<div>
    <input type="hidden" name="{{ $name }}" value="0" />
    <label for="{{ $name }}" class="inline-flex items-center gap-2 cursor-pointer">
        <input
            type="checkbox"
            name="{{ $name }}"
            id="{{ $name }}"
            value="1"
            @checked(old($name, $checked))
            class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500"
        />
        <span class="text-sm font-semibold text-gray-700">{{ $label }}</span>
    </label>
    @if ($hint)
        <p class="text-xs text-gray-400 mt-1">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
    @enderror
</div>
