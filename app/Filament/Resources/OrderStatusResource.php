<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderStatusResource\Pages;
use App\Models\OrderStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class OrderStatusResource extends Resource
{
    protected static ?string $model = OrderStatus::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationLabel = 'Order Statuses';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->required()
                ->maxLength(100),

            TextInput::make('key')
                ->required()
                ->maxLength(50)
                ->unique(ignoreRecord: true)
                ->helperText('Lowercase key used internally, e.g. "pending", "dispatched"'),

            Select::make('color')
                ->required()
                ->options([
                    'gray'    => 'Gray',
                    'warning' => 'Warning (Orange)',
                    'info'    => 'Info (Blue)',
                    'primary' => 'Primary (Green)',
                    'success' => 'Success (Green)',
                    'danger'  => 'Danger (Red)',
                ]),

            TextInput::make('sort_order')
                ->numeric()
                ->default(0),

            Toggle::make('is_active')
                ->label('Active')
                ->default(true),

            Toggle::make('is_default')
                ->label('Default for new orders')
                ->helperText('Only one status can be the default. Enabling this will unset any existing default.')
                ->default(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('name')
                    ->searchable(),

                TextColumn::make('key')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('color')
                    ->badge()
                    ->color(fn(string $state) => $state),

                ToggleColumn::make('is_active')
                    ->label('Active'),

                ToggleColumn::make('is_default')
                    ->label('Default'),
            ])
            ->defaultSort('sort_order')
            ->actions([
                EditAction::make()->icon('heroicon-s-pencil-square'),
            ])
            ->reorderable('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOrderStatuses::route('/'),
            'create' => Pages\CreateOrderStatus::route('/create'),
            'edit'   => Pages\EditOrderStatus::route('/{record}/edit'),
        ];
    }
}
