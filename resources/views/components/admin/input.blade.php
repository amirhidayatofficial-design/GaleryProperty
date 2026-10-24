@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'required' => false,
    'hint' => null,
])

<div>
    <label for="{{ $name }}" class="block text-sm font-semibold text-gray-700 mb-1">
        {{ $label }} @if ($required)<span class="text-rose-500">*</span>@endif
    </label>
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        @if ($type !== 'file') value="{{ old($name, $value) }}" @endif
        @required($required)
        {{ $attributes->merge(['class' => 'w-full text-sm border-gray-200 rounded-lg focus:ring-primary-500 focus:border-primary-500' . ($errors->has($name) ? ' border-rose-400 ring-rose-200' : '')]) }}
    />
    @if ($hint)
        <p class="text-xs text-gray-400 mt-1">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
    @enderror
</div>
