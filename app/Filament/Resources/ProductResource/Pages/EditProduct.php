<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Site;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
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

            DeleteAction::make(),
            RestoreAction::make(),
            ForceDeleteAction::make()->label('Permanently Delete'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $existingPrices = $this->record->prices()->get()->keyBy('site_id');

        $data['selected_sites'] = $existingPrices->keys()->toArray();
        $data['price_all_sites'] = true;

        $firstPrice = $existingPrices->first();
        if ($firstPrice) {
            $data['mrp']            = $firstPrice->mrp;
            $data['discount_type']  = $firstPrice->discount_type;
            $data['discount_value'] = $firstPrice->discount_value;
            $data['our_price']      = $firstPrice->our_price;
        }

        $data['site_prices'] = Site::where('is_active', true)
            ->get()
            ->map(function ($site) use ($existingPrices) {
                $price = $existingPrices->get($site->id);
                return [
                    'site_id'        => $site->id,
                    'site_name'      => $site->name,
                    'mrp'            => $price?->mrp,
                    'discount_type'  => $price?->discount_type ?? 'percentage',
                    'discount_value' => $price?->discount_value ?? 0,
                    'our_price'      => $price?->our_price,
                ];
            })
            ->toArray();

        return $data;
    }

    protected function afterSave(): void
    {
        $state       = $this->form->getRawState();
        $allSites    = (bool) ($state['price_all_sites'] ?? true);
        $selectedIds = $state['selected_sites'] ?? [];

        $this->record->prices()
            ->whereNotIn('site_id', $selectedIds)
            ->delete();

        if (empty($selectedIds)) return;

        if ($allSites) {
            $mrp      = $state['mrp'] ?? null;
            $ourPrice = $state['our_price'] ?? null;

            if (!$mrp || !$ourPrice) return;

            $type     = $state['discount_type'] ?? 'percentage';
            $discount = (float) ($state['discount_value'] ?? 0);

            Site::where('is_active', true)->whereIn('id', $selectedIds)
                ->each(function (Site $site) use ($mrp, $type, $discount, $ourPrice) {
                    $this->record->prices()->updateOrCreate(
                        ['site_id' => $site->id],
                        [
                            'mrp'            => $mrp,
                            'discount_type'  => $type,
                            'discount_value' => $discount,
                            'our_price'      => $ourPrice,
                        ]
                    );
                });
        } else {
            foreach ($state['site_prices'] ?? [] as $row) {
                if (!in_array($row['site_id'], $selectedIds)) continue;
                if (empty($row['mrp']) || empty($row['our_price'])) continue;

                $this->record->prices()->updateOrCreate(
                    ['site_id' => $row['site_id']],
                    [
                        'mrp'            => $row['mrp'],
                        'discount_type'  => $row['discount_type'] ?? 'percentage',
                        'discount_value' => (float) ($row['discount_value'] ?? 0),
                        'our_price'      => $row['our_price'],
                    ]
                );
            }
        }
    }
}
