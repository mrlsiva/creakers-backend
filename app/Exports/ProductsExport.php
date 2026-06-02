<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function query()
    {
        return Product::with('category')->orderBy('sort_order')->orderBy('name');
    }

    public function headings(): array
    {
        return ['S/N', 'Category', 'Name', 'Slug', 'Per', 'Description', 'Sort Order', 'Is Active'];
    }

    public function map($product): array
    {
        static $i = 0;
        $i++;
        return [
            $i,
            $product->category->name ?? '',
            $product->name,
            $product->slug,
            $product->per ?? '',
            $product->description ?? '',
            $product->sort_order,
            $product->is_active ? 'Yes' : 'No',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
