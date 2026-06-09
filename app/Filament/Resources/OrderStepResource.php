<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderStepResource\Pages;
use App\Models\OrderStep;
use App\Models\Site;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrderStepResource extends Resource
{
    protected static ?string $model = OrderStep::class;
    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';
    protected static ?string $navigationLabel = 'How to Order Steps';
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('site_id')
                ->label('Site')
                ->options(Site::where('is_active', true)->pluck('name', 'id'))
                ->required()
                ->columnSpan(1),

            TextInput::make('sort_order')
                ->label('Step Order')
                ->numeric()
                ->default(0)
                ->helperText('Step I = 1, Step II = 2, ...')
                ->columnSpan(1),

            TextInput::make('title')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            Textarea::make('description')
                ->rows(3)
                ->columnSpanFull(),

            TextInput::make('icon')
                ->label('Step Icon (Font Awesome)')
                ->placeholder('e.g. shopping-cart, box, truck, star')
                ->helperText('Enter a Font Awesome icon name without the "fa-" prefix.')
                ->maxLength(100)
                ->columnSpanFull(),

            Toggle::make('is_active')
                ->default(true)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->label('Step')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('icon')
                    ->label('Icon')
                    ->badge()
                    ->color('gray')
                    ->prefix('fa-'),

                TextColumn::make('title')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('description')
                    ->limit(60)
                    ->color('gray'),

                TextColumn::make('site.name')
                    ->label('Site')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('site')->relationship('site', 'name'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOrderSteps::route('/'),
            'create' => Pages\CreateOrderStep::route('/create'),
            'edit'   => Pages\EditOrderStep::route('/{record}/edit'),
        ];
    }
}
