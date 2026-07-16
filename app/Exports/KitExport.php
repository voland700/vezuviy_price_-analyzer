<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class KitExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(private Builder $query) {}

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return ['ID магазина', 'Название', 'SKU 1', 'SKU 2', 'Цена 1', 'Цена 2', 'Цена магазина', 'Цена поставщика', 'Сравнение цен'];
    }

    public function map($row): array
    {
        return [
            $row->shop_id,
            $row->name,
            $row->sku_1,
            $row->sku_2,
            (int) $row->price_1,
            (int) $row->price_2,
            (int) $row->shop_price,
            (int) $row->supplier_price,
            (string) ($row->info ?? ''),
        ];
    }
}
