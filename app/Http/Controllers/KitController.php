<?php

namespace App\Http\Controllers;

use App\Exports\KitExport;
use App\Http\Controllers\Concerns\AppliesSorting;
use App\Models\Kit;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class KitController extends Controller
{
    use AppliesSorting;

    private const SORTABLE = ['name', 'info'];

    private const DEFAULT_SORT = 'name';

    protected function buildQuery(Request $request): Builder
    {
        return Kit::query()
            ->tap(fn ($q) => $this->applySorting($q, $request, self::SORTABLE, self::DEFAULT_SORT));
    }

    public function index(Request $request): View
    {
        $rows = $this->buildQuery($request)->paginate(50)->withQueryString();
        $sortMeta = $this->sortMeta($request, self::DEFAULT_SORT);

        return view('kits.index', compact('rows', 'sortMeta'));
    }

    public function export(Request $request): BinaryFileResponse
    {
        return (new KitExport($this->buildQuery($request)))
            ->download($this->exportFilename('kits'));
    }
}
