@props(['value'])

@if ($value === 'больше')
    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">{{ $value }}</span>
@elseif($value === 'меньше')
    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">{{ $value }}</span>
@else
    <span class="text-gray-400 dark:text-gray-600">—</span>
@endif
