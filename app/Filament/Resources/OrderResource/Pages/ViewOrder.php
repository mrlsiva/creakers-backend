<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\RelationManagers\RelationGroup;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function getRelationManagers(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_enquiry')
                ->label('Download Enquiry')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn() => route('enquiry.download', $this->record->order_number))
                ->openUrlInNewTab(),

            EditAction::make()->label('Update Status'),
        ];
    }
}
