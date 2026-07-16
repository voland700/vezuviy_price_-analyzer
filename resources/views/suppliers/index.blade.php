@php use Illuminate\Support\Facades\Request; @endphp
<x-app-layout>
    <x-slot name="title">Товары поставщика — PriceAnalyzer</x-slot>

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Все товары производителя Везувий</h1>
            <x-export-button route="suppliers.export" />
        </div>

        <!-- Filters -->
        <form method="GET" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Название</label>
                <input type="text" name="name" value="{{ request('name') }}" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" placeholder="вхождение">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">SKU (точно)</label>
                <input type="text" name="sku" value="{{ request('sku') }}" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Бренд</label>
                <select name="brand" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                    <option value="">— все —</option>
                    @foreach ($brands as $b)
                        <option value="{{ $b }}" @selected(request('brand') === $b)>{{ $b }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Категория</label>
                <select name="category" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                    <option value="">— все —</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c }}" @selected(request('category') === $c)>{{ $c }}</option>
                    @endforeach
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
                <a href="{{ route('suppliers.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-md">Сбросить</a>
            </div>
        </form>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs uppercase text-gray-500 dark:text-gray-400">
                    <tr>
                        <x-sortable-th field="name" :sort="$sortMeta['sort']" :direction="$sortMeta['direction']">Название</x-sortable-th>
                        <x-sortable-th field="sku" :sort="$sortMeta['sort']" :direction="$sortMeta['direction']">SKU</x-sortable-th>
                        <x-sortable-th field="brand" :sort="$sortMeta['sort']" :direction="$sortMeta['direction']">Бренд</x-sortable-th>
                        <x-sortable-th field="category" :sort="$sortMeta['sort']" :direction="$sortMeta['direction']">Категория</x-sortable-th>
                        <x-sortable-th field="available" :sort="$sortMeta['sort']" :direction="$sortMeta['direction']">Доступен</x-sortable-th>
                        <th class="px-3 py-2 text-left">Цена</th>
                        <th class="px-3 py-2 text-left">Сайт</th>
                        <th class="px-3 py-2 text-left"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    @forelse ($rows as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-3 py-2 text-gray-800 dark:text-gray-200">{{ $row->name }}</td>
                            <td class="px-3 py-2 font-mono text-xs text-gray-600 dark:text-gray-300">{{ $row->sku }}</td>
                            <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ $row->brand }}</td>
                            <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ $row->category }}</td>
                            <td class="px-3 py-2"><x-bool-icon :value="$row->available" /></td>
                            <td class="px-3 py-2 font-semibold whitespace-nowrap text-gray-800 dark:text-gray-100"><x-price :value="$row->price" /></td>
                            <td class="px-3 py-2">
                                @if ($row->url)
                                    <a href="{{ $row->url }}" target="_blank" rel="noopener" class="inline-flex items-center text-indigo-600 dark:text-indigo-400 hover:underline" title="Открыть на сайте">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-3-6l3 3m0 0l-6.75 6.75M15 4.5h4.5V9" /></svg>
                                    </a>
                                @endif
                            </td>
                            <td class="px-3 py-2">
                                <a href="{{ route('suppliers.show', $row) }}" class="px-2 py-1 text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline">Детали →</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-3 py-6 text-center text-gray-400">Нет данных</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $rows->links() }}</div>
    </div>
</x-app-layout>
