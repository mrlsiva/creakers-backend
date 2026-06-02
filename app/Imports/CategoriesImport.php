<?php

namespace App\Imports;

use App\Models\Category;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

class CategoriesImport implements ToModel, WithHeadingRow, SkipsEmptyRows, WithValidation
{
    public function model(array $row): Category
    {
        $name = trim($row['name'] ?? '');
        $slug = trim($row['slug'] ?? '') ?: Str::slug($name);
        $isActive = strtolower(trim($row['is_active'] ?? 'yes')) !== 'no';

        return new Category([
            'name'       => $name,
            'slug'       => $slug,
            'sort_order' => (int) ($row['sort_order'] ?? 0),
            'is_active'  => $isActive,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string',
        ];
    }
}
