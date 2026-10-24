@props([
    'name',
    'label',
    'options' => [],
    'value' => null,
    'required' => false,
    'placeholder' => null,
    'hint' => null,
])

@php($selected = (string) old($name, $value))

<div>
    <label for="{{ $name }}" class="block text-sm font-semibold text-gray-700 mb-1">
        {{ $label }} @if ($required)<span class="text-rose-500">*</span>@endif
    </label>
    <select
        name="{{ $name }}"
        id="{{ $name }}"
        @required($required)
        {{ $attributes->merge(['class' => 'w-full text-sm border-gray-200 rounded-lg focus:ring-primary-500 focus:border-primary-500 text-gray-800' . ($errors->has($name) ? ' border-rose-400 ring-rose-200' : '')]) }}
    >
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($selected === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @if ($hint)
        <p class="text-xs text-gray-400 mt-1">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
    @enderror
</div>
