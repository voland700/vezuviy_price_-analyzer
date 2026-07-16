<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait AppliesSorting
{
    /**
     * Apply validated sorting to the query based on ?sort & ?direction.
     *
     * @param  array<int,string>  $sortable  whitelist of allowed sort columns
     */
    protected function applySorting(Builder $query, Request $request, array $sortable, string $default = 'id'): Builder
    {
        $sort = $request->input('sort', $default);
        $direction = strtolower((string) $request->input('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (! in_array($sort, $sortable, true)) {
            $sort = $default;
        }

        return $query->orderBy($sort, $direction);
    }

    protected function exportFilename(string $table): string
    {
        return $table.'_'.now()->format('Y-m-d_H-i-s').'.xlsx';
    }

    protected function sortMeta(Request $request, string $default = 'id'): array
    {
        return [
            'sort' => $request->input('sort', $default),
            'direction' => strtolower((string) $request->input('direction', 'asc')) === 'desc' ? 'desc' : 'asc',
        ];
    }
}
