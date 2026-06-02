<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\ProductPrice;
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
                ->minValue(0),

            TextInput::make('discount_percent')
                ->label('Discount %')
                ->numeric()
                ->default(0)
                ->minValue(0)
                ->maxValue(100)
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                    $mrp = (float) $get('mrp');
                    if ($mrp > 0) {
                        $set('our_price', round($mrp * (1 - $state / 100), 2));
                    }
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
                TextColumn::make('discount_percent')->suffix('%')->label('Discount'),
                TextColumn::make('our_price')->money('INR')->label('Our Price'),
            ])
            ->headerActions([CreateAction::make()])
            ->actions([EditAction::make(), DeleteAction::make()]);
    }
}
