@props([
    'name',
    'label',
    'options' => [],
    'placeholder' => 'Semua',
])

<div class="sm:w-52">
    <label for="filter_{{ $name }}" class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">{{ $label }}</label>
    <select name="{{ $name }}" id="filter_{{ $name }}" class="w-full py-2 px-3 text-sm border-gray-200 rounded-lg focus:ring-primary-500 focus:border-primary-500 text-gray-700">
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) request($name) === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
</div>
