<x-app-layout>
    <x-slot name="title">Комплекты — PriceAnalyzer</x-slot>

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Комплекты товаров</h1>
            <x-export-button route="kits.export" />
        </div>

        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
            Данные товаров производителя Везувий предлагаемые в продаже в комплекте
        </p>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs uppercase text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-3 py-2 text-left">ID</th>
                        <x-sortable-th field="name" :sort="$sortMeta['sort']" :direction="$sortMeta['direction']">Название</x-sortable-th>
                        <th class="px-3 py-2 text-left">SKU 1</th>
                        <th class="px-3 py-2 text-left">SKU 2</th>
                        <th class="px-3 py-2 text-left">Цена 1</th>
                        <th class="px-3 py-2 text-left">Цена 2</th>
                        <th class="px-3 py-2 text-left">Цена магазина</th>
                        <th class="px-3 py-2 text-left">Цена поставщика</th>
                        <x-sortable-th field="info" :sort="$sortMeta['sort']" :direction="$sortMeta['direction']">Сравнение</x-sortable-th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    @forelse ($rows as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-3 py-2 text-gray-500 dark:text-gray-400">{{ $row->shop_id }}</td>
                            <td class="px-3 py-2 text-gray-800 dark:text-gray-200">{{ $row->name }}</td>
                            <td class="px-3 py-2 font-mono text-xs text-gray-600 dark:text-gray-300">{{ $row->sku_1 }}</td>
                            <td class="px-3 py-2 font-mono text-xs text-gray-600 dark:text-gray-300">{{ $row->sku_2 }}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-gray-800 dark:text-gray-100"><x-price :value="$row->price_1" /></td>
                            <td class="px-3 py-2 whitespace-nowrap text-gray-800 dark:text-gray-100"><x-price :value="$row->price_2" /></td>
                            <td class="px-3 py-2 whitespace-nowrap text-gray-800 dark:text-gray-100"><x-price :value="$row->shop_price" /></td>
                            <td class="px-3 py-2 whitespace-nowrap text-gray-800 dark:text-gray-100"><x-price :value="$row->supplier_price" /></td>
                            <td class="px-3 py-2"><x-info-badge :value="$row->info" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-3 py-6 text-center text-gray-400">Нет данных</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $rows->links() }}</div>
    </div>
</x-app-layout>
