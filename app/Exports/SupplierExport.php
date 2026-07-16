<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SupplierExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(private Builder $query) {}

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return ['Название', 'SKU', 'Бренд', 'Категория', 'Доступен', 'Склад', 'URL', 'Цена'];
    }

    public function map($row): array
    {
        return [
            $row->name,
            $row->sku,
            $row->brand,
            $row->category,
            (int) $row->available,
            (int) $row->store,
            (string) $row->url,
            (int) $row->price,
        ];
    }
}
