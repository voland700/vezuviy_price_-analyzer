<x-app-layout>
    <x-slot name="title">Дашборд — PriceAnalyzer</x-slot>

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-2">Анализ товаров производителя Везувий</h1>

        @php
            $isToday = optional($lastUpdate)->isToday();
        @endphp

        <!-- Widgets -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-8">
            <!-- Date -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Дата обновления данных</div>
                <div class="mt-1 text-lg font-semibold {{ $isToday ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    @if ($lastUpdate)
                        {{ $lastUpdate->translatedFormat('d F Y H:i') }}
                        @if (! $isToday) <span class="text-xs">(устарела)</span> @endif
                    @else
                        —
                    @endif
                </div>
            </div>

            <!-- Price mismatch -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Не соответствие цены</div>
                <div class="mt-1 flex items-center gap-3">
                    <span class="text-lg font-semibold text-red-600 dark:text-red-400">↑ {{ $priceMore }}</span>
                    <span class="text-xs text-gray-400">больше</span>
                    <span class="text-lg font-semibold text-blue-600 dark:text-blue-400 ml-2">↓ {{ $priceLess }}</span>
                    <span class="text-xs text-gray-400">меньше</span>
                </div>
            </div>

            <!-- Suppliers count -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Количество товаров</div>
                <div class="mt-1 text-2xl font-bold text-gray-800 dark:text-gray-100">{{ number_format($suppliersCount, 0, '.', ' ') }}</div>
            </div>

            <!-- In shop -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">В магазине</div>
                <div class="mt-1 text-2xl font-bold text-gray-800 dark:text-gray-100">{{ number_format($inShop, 0, '.', ' ') }}</div>
            </div>

            <!-- Not at manufacturer -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Нет у производителя</div>
                <div class="mt-1 text-2xl font-bold text-gray-800 dark:text-gray-100">{{ number_format($notAtManufacturer, 0, '.', ' ') }}</div>
            </div>

            <!-- Not in shop -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Нет в магазине</div>
                <div class="mt-1 text-2xl font-bold text-gray-800 dark:text-gray-100">{{ number_format($notInShop, 0, '.', ' ') }}</div>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Бренды производителя</h2>
                <div class="h-72">
                    <canvas id="brandsChart" data-json='@json($brands)'></canvas>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Данные о товарах</h2>
                <div class="h-72">
                    <canvas id="summaryChart" data-json='@json($summary)'></canvas>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite(['resources/js/dashboard.js'])
    @endpush
</x-app-layout>
