<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Site;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;
use Illuminate\Support\Collection;

class ProductsImport implements OnEachRow, WithHeadingRow, SkipsEmptyRows, WithValidation
{
    private array $categoryCache = [];
    private array $siteCache = [];
    private ?Collection $allActiveSites = null;

    public function onRow(Row $row): void
    {
        $data = $row->toArray();

        $name = trim($data['name'] ?? '');
        if (!$name) return;

        $categoryName = trim($data['category'] ?? '');
        $categoryId = null;

        if ($categoryName) {
            if (!isset($this->categoryCache[$categoryName])) {
                $this->categoryCache[$categoryName] = Category::where('name', $categoryName)->value('id');
            }
            $categoryId = $this->categoryCache[$categoryName];
        }

        $slug = trim($data['slug'] ?? '') ?: Str::slug($name);
        $isActive = strtolower(trim($data['is_active'] ?? 'yes')) !== 'no';

        $product = Product::firstOrCreate(
            ['slug' => $slug],
            [
                'category_id' => $categoryId,
                'name'        => $name,
                'per'         => trim($data['per'] ?? '') ?: null,
                'description' => trim($data['description'] ?? '') ?: null,
                'sort_order'  => (int) ($data['sort_order'] ?? 0),
                'is_active'   => $isActive,
            ]
        );

        $mrp           = (float) ($data['mrp'] ?? 0);
        $discountType  = trim($data['discount_type'] ?? 'percentage');
        $discountValue = (float) ($data['discount_value'] ?? 0);
        $ourPrice      = (float) ($data['our_price'] ?? 0);

        if (!in_array($discountType, ['percentage', 'flat'])) {
            $discountType = 'percentage';
        }

        $allSites = strtolower(trim($data['all_sites'] ?? 'no')) === 'yes';

        if ($allSites && $mrp > 0) {
            $this->allActiveSites ??= Site::where('is_active', true)->get();

            foreach ($this->allActiveSites as $site) {
                ProductPrice::updateOrCreate(
                    ['product_id' => $product->id, 'site_id' => $site->id],
                    [
                        'mrp'            => $mrp,
                        'discount_type'  => $discountType,
                        'discount_value' => $discountValue,
                        'our_price'      => $ourPrice,
                    ]
                );
            }
        } else {
            $siteName = trim($data['site'] ?? '');
            if ($siteName) {
                if (!isset($this->siteCache[$siteName])) {
                    $this->siteCache[$siteName] = Site::where('name', $siteName)->value('id');
                }
                $siteId = $this->siteCache[$siteName];

                if ($siteId) {
                    ProductPrice::updateOrCreate(
                        ['product_id' => $product->id, 'site_id' => $siteId],
                        [
                            'mrp'            => $mrp,
                            'discount_type'  => $discountType,
                            'discount_value' => $discountValue,
                            'our_price'      => $ourPrice,
                        ]
                    );
                }
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string',
        ];
    }
}
