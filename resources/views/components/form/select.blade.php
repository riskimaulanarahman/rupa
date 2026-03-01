@props([
    'name',
    'label' => null,
    'value' => null,
    'required' => false,
    'disabled' => false,
])

@php
    $themeRing = match($businessType ?? 'clinic') {
        'salon' => 'focus:ring-purple-500/20 focus:border-purple-400',
        'barbershop' => 'focus:ring-blue-500/20 focus:border-blue-400',
        default => 'focus:ring-rose-500/20 focus:border-rose-400',
    };
@endphp

<div>
    @if($label)
        <label for="{{ $name }}" class="block text-sm max-sm:text-xs font-medium text-gray-700 mb-2 max-sm:mb-1.5">
            {{ $label }}
            @if($required)<span class="text-red-500">*</span>@endif
        </label>
    @endif
    <select
        id="{{ $name }}"
        name="{{ $name }}"
        @if($required) required @endif
        @if($disabled) disabled @endif
        {{ $attributes->merge(['class' => "w-full pl-4 pr-10 py-2.5 max-sm:py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 {$themeRing} transition appearance-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"]) }}
        @error($name) aria-invalid="true" @enderror
    >
        {{ $slot }}
    </select>
    @error($name)
        <p class="mt-1 text-sm max-sm:text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>
