<?php

namespace App\Http\Controllers;

use App\Exports\SupplierExport;
use App\Http\Controllers\Concerns\AppliesSorting;
use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SupplierController extends Controller
{
    use AppliesSorting;

    private const SORTABLE = ['name', 'sku', 'brand', 'category', 'available', 'price'];

    private const DEFAULT_SORT = 'name';

    protected function buildQuery(Request $request): Builder
    {
        return Supplier::query()
            ->when($request->filled('name'), fn ($q) => $q->where('name', 'like', '%'.$request->name.'%'))
            ->when($request->filled('sku'), fn ($q) => $q->where('sku', $request->sku))
            ->when($request->filled('brand'), fn ($q) => $q->where('brand', $request->brand))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->category))
            ->when($request->filled('available') && $request->available !== '', fn ($q) => $q->where('available', $request->available))
            ->tap(fn ($q) => $this->applySorting($q, $request, self::SORTABLE, self::DEFAULT_SORT));
    }

    public function index(Request $request): View
    {
        $rows = $this->buildQuery($request)->paginate(50)->withQueryString();
        $sortMeta = $this->sortMeta($request, self::DEFAULT_SORT);
        $brands = Supplier::select('brand')->whereNotNull('brand')->distinct()->orderBy('brand')->pluck('brand');
        $categories = Supplier::select('category')->whereNotNull('category')->distinct()->orderBy('category')->pluck('category');

        return view('suppliers.index', compact('rows', 'sortMeta', 'brands', 'categories'));
    }

    public function show(Supplier $supplier): View
    {
        return view('suppliers.show', ['supplier' => $supplier]);
    }

    public function showBySku(string $sku): View|RedirectResponse
    {
        $supplier = Supplier::where('sku', $sku)->firstOrFail();

        return view('suppliers.show', ['supplier' => $supplier]);
    }

    public function export(Request $request): BinaryFileResponse
    {
        return (new SupplierExport($this->buildQuery($request)))
            ->download($this->exportFilename('suppliers'));
    }
}
