@php
    $pictures = is_array($supplier->pictures) ? $supplier->pictures : [];
    $params = is_array($supplier->params) ? $supplier->params : [];
@endphp
<x-app-layout>
    <x-slot name="title">{{ $supplier->name }} — PriceAnalyzer</x-slot>

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('suppliers.index') }}" class="inline-flex items-center gap-1 text-sm text-indigo-600 dark:text-indigo-400 hover:underline mb-4">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
            Назад к списку
        </a>

        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">{{ $supplier->name }}</h1>

        @if (! empty($pictures))
            <!-- Fancybox gallery -->
            <div class="flex flex-wrap gap-3 mb-4">
                @foreach ($pictures as $i => $pic)
                    <a data-fancybox="gallery" href="{{ $pic }}" class="block">
                        <img src="{{ $pic }}" alt="" class="h-24 w-24 object-cover rounded-md border border-gray-200 dark:border-gray-700">
                    </a>
                @endforeach
            </div>

            <!-- Links list -->
            <details class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <summary class="cursor-pointer text-sm font-medium text-gray-600 dark:text-gray-300">Ссылки на изображения ({{ count($pictures) }})</summary>
                <ul class="mt-3 list-disc list-inside text-xs text-blue-600 dark:text-blue-400 space-y-1 break-all">
                    @foreach ($pictures as $pic)
                        <li><a href="{{ $pic }}" target="_blank" rel="noopener" class="hover:underline">{{ $pic }}</a></li>
                    @endforeach
                </ul>
            </details>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main info -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow p-6 space-y-4">
                <!-- SKU with copy -->
                <div x-data="clipboard">
                    <div class="text-xs text-gray-500 dark:text-gray-400">SKU</div>
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-lg text-gray-800 dark:text-gray-100">{{ $supplier->sku }}</span>
                        <button type="button" @click="copy('{{ $supplier->sku }}')" class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400" title="Копировать">
                            <svg x-show="!copied" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m9.75 0l-3-3m3 3l-3 3" /></svg>
                            <svg x-show="copied" class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Бренд</div>
                        <div class="text-gray-800 dark:text-gray-200">{{ $supplier->brand }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Категория товара</div>
                        <div class="text-gray-800 dark:text-gray-200">{{ $supplier->category }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Доступность у производителя</div>
                        <div class="font-medium {{ $supplier->available ? 'text-green-600 dark:text-green-400' : 'text-gray-400 dark:text-gray-500' }}">
                            {{ $supplier->available ? 'В наличии' : 'Нет в наличии' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Готовность к отгрузке</div>
                        <div class="font-medium {{ $supplier->store ? 'text-green-600 dark:text-green-400' : 'text-gray-400 dark:text-gray-500' }}">
                            {{ $supplier->store ? 'На складе' : 'Нет на складе' }}
                        </div>
                    </div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Товар на сайте производителя</div>
                    @if ($supplier->url)
                        <a href="{{ $supplier->url }}" target="_blank" rel="noopener" class="text-indigo-600 dark:text-indigo-400 hover:underline break-all">{{ $supplier->url }}</a>
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
                </div>

                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Цена товара</div>
                    <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($supplier->price, 0, '.', ' ') }} ₽</div>
                </div>

                @if ($supplier->description)
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Описание товара</div>
                        <div class="text-gray-700 dark:text-gray-300 text-sm whitespace-pre-line">{{ $supplier->description }}</div>
                    </div>
                @endif
            </div>

            <!-- Params -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100 mb-3">Характеристики</h2>
                @if (! empty($params))
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($params as $p)
                                <tr>
                                    <td class="py-2 pr-2 text-gray-500 dark:text-gray-400 align-top">{{ $p['name'] ?? '' }}</td>
                                    <td class="py-2 text-gray-800 dark:text-gray-200 align-top">{{ $p['value'] ?? '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-sm text-gray-400">Нет характеристик</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
