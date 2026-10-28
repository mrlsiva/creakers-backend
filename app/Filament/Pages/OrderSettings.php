<?php

namespace App\Filament\Pages;

use App\Models\Site;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class OrderSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Order Settings';
    protected static ?string $title = 'Order Settings';
    protected static string $view = 'filament.pages.order-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->loadSite(Site::orderBy('id')->value('id'));
    }

    protected function loadSite($siteId): void
    {
        $site = $siteId ? Site::find($siteId) : null;

        $this->form->fill([
            'site_id' => $site?->id,
            'orders_enabled' => $site?->orders_enabled ?? true,
            'orders_closed_message' => $site?->orders_closed_message,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Select::make('site_id')
                    ->label('Site')
                    ->options(Site::pluck('name', 'id'))
                    ->required()
                    ->selectablePlaceholder(false)
                    ->live()
                    ->afterStateUpdated(fn($state) => $this->loadSite($state)),

                Section::make('Online Orders')
                    ->description('Turn off to stop new orders on the selected site. Order tracking keeps working.')
                    ->schema([
                        Toggle::make('orders_enabled')
                            ->label('Accept online orders')
                            ->onColor('success')
                            ->offColor('danger')
                            ->live(),

                        Textarea::make('orders_closed_message')
                            ->label('Closed message')
                            ->rows(8)
                            ->placeholder('🎆 ONLINE ORDERS CLOSED! 🎆')
                            ->helperText('Shown to customers on the website while orders are closed.')
                            ->required(fn(Get $get) => !$get('orders_enabled')),
                    ]),
            ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $site = Site::findOrFail($state['site_id']);

        $site->update([
            'orders_enabled' => $state['orders_enabled'],
            'orders_closed_message' => $state['orders_closed_message'],
        ]);

        $status = $site->orders_enabled ? 'open' : 'closed';

        Notification::make()->title("Online orders are now {$status} for {$site->name}")->success()->send();
    }
}
