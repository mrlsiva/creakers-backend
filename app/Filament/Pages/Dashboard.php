<?php

namespace App\Filament\Pages;

use App\Models\Site;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function filtersForm(Form $form): Form
    {
        return $form->schema([
            Select::make('site_id')
                ->label('Site')
                ->placeholder('All Sites')
                ->options(fn () => Site::where('is_active', true)->orderBy('name')->pluck('name', 'id')->toArray())
                ->live(),
        ]);
    }
}
