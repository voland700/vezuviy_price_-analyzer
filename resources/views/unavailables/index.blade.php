<x-app-layout>
    <x-slot name="title">Нет у производителя — PriceAnalyzer</x-slot>

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Товары отсутствующие у производителя Везувий</h1>
            <x-export-button route="unavailables.export" />
        </div>

        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
            На данной странице показаны данные товаров, которые отсутствуют у поставщика, но есть в магазине. Возможно, сняты с производства.
        </p>

        <!-- Filters -->
        <form method="GET" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">ID магазина (точно)</label>
                <input type="text" name="shop_id" value="{{ request('shop_id') }}" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Название</label>
                <input type="text" name="name" value="{{ request('name') }}" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" placeholder="вхождение">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">SKU (точно)</label>
                <input type="text" name="sku" value="{{ request('sku') }}" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Активен</label>
                <select name="active" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                    <option value="">— все —</option>
                    <option value="1" @selected(request('active') === '1')>Да</option>
                    <option value="0" @selected(request('active') === '0')>Нет</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Доступен</label>
                <select name="available" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                    <option value="">— все —</option>
                    <option value="1" @selected(request('available') === '1')>Да</option>
                    <option value="0" @selected(request('available') === '0')>Нет</option>
                </select>
            </div>
            <div class="lg:col-span-5 flex gap-2 mt-1">
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md">Применить</button>
                <a href="{{ route('unavailables.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-md">Сбросить</a>
            </div>
        </form>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs uppercase text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-3 py-2 text-left">ID</th>
                        <x-sortable-th field="name" :sort="$sortMeta['sort']" :direction="$sortMeta['direction']">Название</x-sortable-th>
                        <x-sortable-th field="active" :sort="$sortMeta['sort']" :direction="$sortMeta['direction']">Активен</x-sortable-th>
                        <x-sortable-th field="available" :sort="$sortMeta['sort']" :direction="$sortMeta['direction']">Доступен</x-sortable-th>
                        <x-sortable-th field="sku" :sort="$sortMeta['sort']" :direction="$sortMeta['direction']">SKU</x-sortable-th>
                        <x-sortable-th field="shop_price" :sort="$sortMeta['sort']" :direction="$sortMeta['direction']">Цена магазина</x-sortable-th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    @forelse ($rows as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-3 py-2 text-gray-500 dark:text-gray-400">{{ $row->shop_id }}</td>
                            <td class="px-3 py-2 text-gray-800 dark:text-gray-200">{{ $row->name }}</td>
                            <td class="px-3 py-2"><x-bool-icon :value="$row->active" /></td>
                            <td class="px-3 py-2"><x-bool-icon :value="$row->available" /></td>
                            <td class="px-3 py-2 font-mono text-xs text-gray-600 dark:text-gray-300">{{ $row->sku }}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-gray-800 dark:text-gray-100"><x-price :value="$row->shop_price" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-3 py-6 text-center text-gray-400">Нет данных</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $rows->links() }}</div>
    </div>
</x-app-layout>
