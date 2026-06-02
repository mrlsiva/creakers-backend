<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductsImport implements ToModel, WithHeadingRow, SkipsEmptyRows, WithValidation
{
    private array $categoryCache = [];

    public function model(array $row): ?Product
    {
        $name = trim($row['name'] ?? '');
        if (!$name) return null;

        $categoryName = trim($row['category'] ?? '');
        $categoryId = null;

        if ($categoryName) {
            if (!isset($this->categoryCache[$categoryName])) {
                $this->categoryCache[$categoryName] = Category::where('name', $categoryName)->value('id');
            }
            $categoryId = $this->categoryCache[$categoryName];
        }

        $slug = trim($row['slug'] ?? '') ?: Str::slug($name);
        $isActive = strtolower(trim($row['is_active'] ?? 'yes')) !== 'no';

        return new Product([
            'category_id' => $categoryId,
            'name'        => $name,
            'slug'        => $slug,
            'per'         => trim($row['per'] ?? '') ?: null,
            'description' => trim($row['description'] ?? '') ?: null,
            'sort_order'  => (int) ($row['sort_order'] ?? 0),
            'is_active'   => $isActive,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string',
        ];
    }
}
