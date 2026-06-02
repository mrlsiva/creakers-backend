<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Order Items';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product_name')->label('Product'),
                TextColumn::make('category_name')->label('Category')->default('—'),
                TextColumn::make('mrp')->money('INR')->label('MRP'),
                TextColumn::make('our_price')->money('INR')->label('Price'),
                TextColumn::make('quantity'),
                TextColumn::make('subtotal')->money('INR'),
            ])
            ->paginated(false);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
