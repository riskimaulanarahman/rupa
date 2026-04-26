@props([
    'name',
    'customers' => collect(),
    'selectedId' => null,
    'placeholder' => 'Pilih pelanggan',
    'label' => null,
    'required' => false,
])

@php
    $customerCollection = collect($customers);
    $selectedCustomer = $selectedId !== null && $selectedId !== ''
        ? $customerCollection->firstWhere('id', (int) $selectedId)
        : null;
    $selectedLabel = $selectedCustomer
        ? "{$selectedCustomer->name} - {$selectedCustomer->phone}"
        : '';
    $customerOptions = $customerCollection
        ->map(fn ($customer) => [
            'id' => (string) $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
        ])
        ->values();
@endphp

<div
    x-data="searchableCustomerSelect({
        customers: @js($customerOptions),
        selectedId: @js($selectedId !== null ? (string) $selectedId : ''),
        selectedLabel: @js($selectedLabel),
        placeholder: @js($placeholder),
    })"
    @click.outside="closeList(true)"
    class="relative"
    data-customer-picker
>
    @if($label)
        <label for="{{ $name }}_search" class="block text-sm max-sm:text-xs font-medium text-gray-700 dark:text-gray-300 mb-2 max-sm:mb-1.5">
            {{ $label }}
            @if($required)<span class="text-red-500">*</span>@endif
        </label>
    @endif

    <input
        type="hidden"
        id="{{ $name }}"
        name="{{ $name }}"
        x-model="selectedId"
        value="{{ $selectedId }}"
    >

    <div class="relative">
        <input
            type="text"
            id="{{ $name }}_search"
            x-model="query"
            value="{{ $selectedLabel }}"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            @focus="openList()"
            @click="openList()"
            @input="handleInput($event.target.value)"
            @keydown.escape.stop="closeList(true)"
            class="w-full pl-4 pr-12 py-2.5 max-sm:py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-400 transition bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error($name) border-red-400 @enderror"
        >
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 dark:text-gray-500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
    </div>

    <div
        x-show="open"
        x-cloak
        class="absolute z-20 mt-2 w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 shadow-lg"
    >
        <div class="max-h-64 overflow-y-auto py-1">
            <template x-if="filteredCustomers.length === 0">
                <div class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                    Pelanggan tidak ditemukan.
                </div>
            </template>

            <template x-for="customer in filteredCustomers" :key="customer.id">
                <button
                    type="button"
                    @click="selectCustomer(customer)"
                    class="flex w-full items-start justify-between gap-3 px-4 py-3 text-left hover:bg-rose-50 dark:hover:bg-gray-700/70"
                >
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-medium text-gray-900 dark:text-gray-100" x-text="customer.name"></span>
                        <span class="block truncate text-xs text-gray-500 dark:text-gray-400" x-text="customer.phone"></span>
                    </span>
                </button>
            </template>
        </div>
    </div>

    @error($name)
        <p class="mt-1 text-sm max-sm:text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>
