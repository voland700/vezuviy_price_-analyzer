<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UnavailableExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(private Builder $query) {}

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return ['ID магазина', 'Название', 'Активен', 'Доступен', 'SKU', 'Цена магазина'];
    }

    public function map($row): array
    {
        return [
            $row->shop_id,
            $row->name,
            (int) $row->active,
            (int) $row->available,
            $row->sku,
            (int) $row->shop_price,
        ];
    }
}
