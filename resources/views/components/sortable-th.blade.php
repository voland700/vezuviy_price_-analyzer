@props(['field' => null, 'sort' => null, 'direction' => null])

@php
$isCurrent = $sort === $field;
$nextDir = ($isCurrent && $direction === 'asc') ? 'desc' : 'asc';
$query = request()->except(['page']);
$query['sort'] = $field;
$query['direction'] = $nextDir;
$url = request()->fullUrlWithQuery($query);
@endphp

<th class="px-3 py-2 text-left">
    <a href="{{ $url }}" class="inline-flex items-center gap-1 hover:text-indigo-600 dark:hover:text-indigo-400 {{ $isCurrent ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : '' }}">
        <span>{{ $slot }}</span>
        @if ($isCurrent)
            @if ($direction === 'asc')
                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path d="M5 8l5-5 5 5H5z" /></svg>
            @else
                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path d="M5 12l5 5 5-5H5z" /></svg>
            @endif
        @else
            <svg class="h-3 w-3 opacity-30" fill="currentColor" viewBox="0 0 20 20"><path d="M5 8l5-5 5 5H5zM5 12l5 5 5-5H5z" /></svg>
        @endif
    </a>
</th>
