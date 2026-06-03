<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        $rows = collect();

        Product::with(['category', 'prices.site'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->each(function ($product) use ($rows) {
                $prices = $product->prices;
                $base   = [
                    $product->category->name ?? '',
                    $product->name,
                    $product->slug,
                    $product->per ?? '',
                    $product->description ?? '',
                    $product->sort_order,
                    $product->is_active ? 'Yes' : 'No',
                ];

                if ($prices->isEmpty()) {
                    $rows->push(array_merge($base, ['Yes', '', '', '', '', '']));
                    return;
                }

                $unique  = $prices->unique(fn($p) => $p->mrp . '|' . $p->discount_type . '|' . $p->discount_value . '|' . $p->our_price);
                $allSame = $unique->count() === 1;

                if ($allSame) {
                    $p = $prices->first();
                    $rows->push(array_merge($base, [
                        'Yes', '', $p->mrp, $p->discount_type, $p->discount_value, $p->our_price,
                    ]));
                } else {
                    foreach ($prices as $p) {
                        $rows->push(array_merge($base, [
                            'No', $p->site->name ?? '', $p->mrp, $p->discount_type, $p->discount_value, $p->our_price,
                        ]));
                    }
                }
            });

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Category', 'Name', 'Slug', 'Per', 'Description',
            'Sort Order', 'Is Active', 'All Sites', 'Site',
            'MRP', 'Discount Type', 'Discount Value', 'Our Price',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
