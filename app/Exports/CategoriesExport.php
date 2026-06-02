<?php

namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CategoriesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Category::orderBy('sort_order')->orderBy('name')->get();
    }

    public function headings(): array
    {
        return ['S/N', 'Name', 'Slug', 'Sort Order', 'Is Active'];
    }

    public function map($category): array
    {
        static $i = 0;
        $i++;
        return [
            $i,
            $category->name,
            $category->slug,
            $category->sort_order,
            $category->is_active ? 'Yes' : 'No',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
