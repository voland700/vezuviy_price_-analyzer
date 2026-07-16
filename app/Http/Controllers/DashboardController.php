<?php

namespace App\Http\Controllers;

use App\Models\Available;
use App\Models\Supplier;
use App\Models\Unavailable;
use App\Models\Undiscovered;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        // Last data update date (Python run time)
        $lastUpdate = Supplier::latest('created_at')->first()?->created_at;

        // Price mismatches
        $priceMore = Available::where('info', 'больше')->count();
        $priceLess = Available::where('info', 'меньше')->count();

        // Counts
        $suppliersCount = Supplier::count();
        $inShop = Available::count() + Unavailable::count();
        $notAtManufacturer = Unavailable::count();
        $notInShop = Undiscovered::count();
        $priceMismatchTotal = Available::whereNotNull('info')->count();

        // Brand distribution (doughnut)
        $brands = Supplier::selectRaw('brand, COUNT(*) as cnt')
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->groupBy('brand')
            ->orderByDesc('cnt')
            ->pluck('cnt', 'brand')
            ->toArray();

        // Summary bar chart
        $summary = [
            'labels' => ['Товаров у производителя', 'Товаров в магазине', 'Нет у производителя', 'Нет в магазине', 'Цена не соответствует'],
            'values' => [$suppliersCount, $inShop, $notAtManufacturer, $notInShop, $priceMismatchTotal],
            'colors' => ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444'],
        ];

        return view('dashboard', [
            'lastUpdate' => $lastUpdate,
            'priceMore' => $priceMore,
            'priceLess' => $priceLess,
            'suppliersCount' => $suppliersCount,
            'inShop' => $inShop,
            'notAtManufacturer' => $notAtManufacturer,
            'notInShop' => $notInShop,
            'priceMismatchTotal' => $priceMismatchTotal,
            'brands' => $brands,
            'summary' => $summary,
        ]);
    }
}
