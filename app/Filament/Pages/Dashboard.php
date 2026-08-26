<?php

namespace App\Filament\Pages;

use App\Models\Site;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Illuminate\Contracts\View\View;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    // Overrides HasFilters::$filters to drop its #[Url] attribute — the
    // selection already persists across visits via session, so we don't
    // need it echoed into the address bar as ?filters[site_id]=...
    public ?array $filters = null;

    protected static string $view = 'filament.pages.dashboard';

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('site_id')
                    ->label(false)
                    ->placeholder('All Sites')
                    ->prefixIcon('heroicon-o-globe-alt')
                    ->native(false)
                    ->options(fn () => Site::where('is_active', true)->orderBy('name')->pluck('name', 'id')->toArray())
                    ->live(),
            ])
            ->columns(1);
    }

    public function getHeader(): ?View
    {
        return view('filament.pages.dashboard-header', [
            'filtersForm' => $this->filtersForm,
            'heading' => $this->getTitle(),
        ]);
    }
}
