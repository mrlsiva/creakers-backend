<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PricesRelationManager extends RelationManager
{
    protected static string $relationship = 'prices';
    protected static ?string $title = 'Site Prices';

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('site_id')
                ->relationship('site', 'name')
                ->required()
                ->searchable()
                ->preload(),

            TextInput::make('mrp')
                ->label('MRP (₹)')
                ->numeric()
                ->required()
                ->minValue(0)
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                    self::recalcOurPrice((float) $state, $get('discount_type'), (float) $get('discount_value'), $set);
                }),

            Select::make('discount_type')
                ->label('Discount Type')
                ->options(['percentage' => 'Percentage (%)', 'flat' => 'Flat (₹)'])
                ->default('percentage')
                ->required()
                ->live()
                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                    self::recalcOurPrice((float) $get('mrp'), $state, (float) $get('discount_value'), $set);
                }),

            TextInput::make('discount_value')
                ->label(fn(callable $get) => $get('discount_type') === 'flat' ? 'Discount (₹)' : 'Discount (%)')
                ->numeric()
                ->default(0)
                ->minValue(0)
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                    self::recalcOurPrice((float) $get('mrp'), $get('discount_type'), (float) $state, $set);
                }),

            TextInput::make('our_price')
                ->label('Our Price (₹)')
                ->numeric()
                ->required()
                ->minValue(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site.name')->sortable(),
                TextColumn::make('mrp')->money('INR')->label('MRP'),
                TextColumn::make('discount_type')->label('Type')->formatStateUsing(fn($state) => ucfirst($state)),
                TextColumn::make('discount_value')->label('Discount'),
                TextColumn::make('our_price')->money('INR')->label('Our Price'),
            ])
            ->headerActions([CreateAction::make()])
            ->actions([EditAction::make(), DeleteAction::make()]);
    }

    private static function recalcOurPrice(float $mrp, ?string $type, float $discount, callable $set): void
    {
        if ($mrp <= 0) return;

        $ourPrice = $type === 'flat'
            ? max(0, $mrp - $discount)
            : round($mrp * (1 - $discount / 100), 2);

        $set('our_price', $ourPrice);
    }
}
