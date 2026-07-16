<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UndiscoveredExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(private Builder $query) {}

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return ['SKU', 'Название', 'Категория', 'Цена', 'Доступен', 'Бренд', 'URL'];
    }

    public function map($row): array
    {
        return [
            $row->sku,
            $row->name,
            $row->category,
            (int) $row->price,
            (int) $row->available,
            $row->brand,
            (string) $row->url,
        ];
    }
}
