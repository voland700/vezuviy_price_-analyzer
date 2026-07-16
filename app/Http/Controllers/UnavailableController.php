<?php

namespace App\Http\Controllers;

use App\Exports\UnavailableExport;
use App\Http\Controllers\Concerns\AppliesSorting;
use App\Models\Unavailable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UnavailableController extends Controller
{
    use AppliesSorting;

    private const SORTABLE = ['name', 'active', 'available', 'sku', 'shop_price'];

    private const DEFAULT_SORT = 'name';

    protected function buildQuery(Request $request): Builder
    {
        return Unavailable::query()
            ->when($request->filled('shop_id'), fn ($q) => $q->where('shop_id', $request->shop_id))
            ->when($request->filled('name'), fn ($q) => $q->where('name', 'like', '%'.$request->name.'%'))
            ->when($request->filled('sku'), fn ($q) => $q->where('sku', $request->sku))
            ->when($request->filled('active') && $request->active !== '', fn ($q) => $q->where('active', $request->active))
            ->when($request->filled('available') && $request->available !== '', fn ($q) => $q->where('available', $request->available))
            ->tap(fn ($q) => $this->applySorting($q, $request, self::SORTABLE, self::DEFAULT_SORT));
    }

    public function index(Request $request): View
    {
        $rows = $this->buildQuery($request)->paginate(50)->withQueryString();
        $sortMeta = $this->sortMeta($request, self::DEFAULT_SORT);

        return view('unavailables.index', compact('rows', 'sortMeta'));
    }

    public function export(Request $request): BinaryFileResponse
    {
        return (new UnavailableExport($this->buildQuery($request)))
            ->download($this->exportFilename('unavailables'));
    }
}
