<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Site;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('toggleActive')
                ->label(fn() => $this->data['is_active'] ?? true ? 'Active' : 'Inactive')
                ->icon(fn() => $this->data['is_active'] ?? true ? 'heroicon-s-check-circle' : 'heroicon-s-x-circle')
                ->color(fn() => $this->data['is_active'] ?? true ? 'success' : 'gray')
                ->outlined()
                ->requiresConfirmation()
                ->modalHeading(fn() => $this->data['is_active'] ?? true ? 'Deactivate Product?' : 'Activate Product?')
                ->modalDescription(fn() => $this->data['is_active'] ?? true
                    ? 'This product will be hidden from all sites.'
                    : 'This product will become visible on all assigned sites.')
                ->modalSubmitActionLabel(fn() => $this->data['is_active'] ?? true ? 'Yes, Deactivate' : 'Yes, Activate')
                ->action(function () {
                    $this->data['is_active'] = !($this->data['is_active'] ?? true);
                }),
        ];
    }

    protected function afterCreate(): void
    {
        $state        = $this->form->getRawState();
        $allSites     = (bool) ($state['price_all_sites'] ?? true);
        $selectedIds  = $state['selected_sites'] ?? [];

        if (empty($selectedIds)) return;

        if ($allSites) {
            $mrp      = $state['mrp'] ?? null;
            $ourPrice = $state['our_price'] ?? null;

            if (!$mrp || !$ourPrice) return;

            $type     = $state['discount_type'] ?? 'percentage';
            $discount = (float) ($state['discount_value'] ?? 0);

            Site::where('is_active', true)->whereIn('id', $selectedIds)
                ->each(function (Site $site) use ($mrp, $type, $discount, $ourPrice) {
                    $this->record->prices()->create([
                        'site_id'        => $site->id,
                        'mrp'            => $mrp,
                        'discount_type'  => $type,
                        'discount_value' => $discount,
                        'our_price'      => $ourPrice,
                    ]);
                });
        } else {
            foreach ($state['site_prices'] ?? [] as $row) {
                if (!in_array($row['site_id'], $selectedIds)) continue;
                if (empty($row['mrp']) || empty($row['our_price'])) continue;

                $this->record->prices()->create([
                    'site_id'        => $row['site_id'],
                    'mrp'            => $row['mrp'],
                    'discount_type'  => $row['discount_type'] ?? 'percentage',
                    'discount_value' => (float) ($row['discount_value'] ?? 0),
                    'our_price'      => $row['our_price'],
                ]);
            }
        }
    }
}
