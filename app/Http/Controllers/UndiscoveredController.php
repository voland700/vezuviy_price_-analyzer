<?php

namespace App\Http\Controllers;

use App\Exports\UndiscoveredExport;
use App\Http\Controllers\Concerns\AppliesSorting;
use App\Models\Supplier;
use App\Models\Undiscovered;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UndiscoveredController extends Controller
{
    use AppliesSorting;

    private const SORTABLE = ['sku', 'name', 'category', 'price', 'available', 'brand'];

    private const DEFAULT_SORT = 'name';

    protected function buildQuery(Request $request): Builder
    {
        return Undiscovered::query()
            ->when($request->filled('sku'), fn ($q) => $q->where('sku', $request->sku))
            ->when($request->filled('name'), fn ($q) => $q->where('name', 'like', '%'.$request->name.'%'))
            ->when($request->filled('category'), fn ($q) => $q->where('category', 'like', '%'.$request->category.'%'))
            ->when($request->filled('available') && $request->available !== '', fn ($q) => $q->where('available', $request->available))
            ->when($request->filled('brand'), fn ($q) => $q->where('brand', $request->brand))
            ->tap(fn ($q) => $this->applySorting($q, $request, self::SORTABLE, self::DEFAULT_SORT));
    }

    public function index(Request $request): View
    {
        $rows = $this->buildQuery($request)->paginate(50)->withQueryString();
        $sortMeta = $this->sortMeta($request, self::DEFAULT_SORT);
        $brands = Supplier::select('brand')->whereNotNull('brand')->distinct()->orderBy('brand')->pluck('brand');

        return view('undiscovereds.index', compact('rows', 'sortMeta', 'brands'));
    }

    public function export(Request $request): BinaryFileResponse
    {
        return (new UndiscoveredExport($this->buildQuery($request)))
            ->download($this->exportFilename('undiscovereds'));
    }
}
