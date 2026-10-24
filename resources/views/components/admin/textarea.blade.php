@props([
    'name',
    'label',
    'value' => null,
    'required' => false,
    'rows' => 4,
    'hint' => null,
])

<div>
    <label for="{{ $name }}" class="block text-sm font-semibold text-gray-700 mb-1">
        {{ $label }} @if ($required)<span class="text-rose-500">*</span>@endif
    </label>
    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        @required($required)
        {{ $attributes->merge(['class' => 'w-full text-sm border-gray-200 rounded-lg focus:ring-primary-500 focus:border-primary-500' . ($errors->has($name) ? ' border-rose-400 ring-rose-200' : '')]) }}
    >{{ old($name, $value) }}</textarea>
    @if ($hint)
        <p class="text-xs text-gray-400 mt-1">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
    @enderror
</div>
