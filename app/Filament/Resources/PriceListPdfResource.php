<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PriceListPdfResource\Pages;
use App\Models\PriceListPdf;
use App\Models\Site;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
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

class PriceListPdfResource extends Resource
{
    protected static ?string $model = PriceListPdf::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-down';
    protected static ?string $navigationLabel = 'Price List PDFs';
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('site_id')
                ->label('Site')
                ->options(Site::where('is_active', true)->pluck('name', 'id'))
                ->required()
                ->columnSpan(1),

            TextInput::make('title')
                ->label('PDF Title')
                ->placeholder('e.g. Price List 2026')
                ->required()
                ->maxLength(255)
                ->columnSpan(1),

            FileUpload::make('file')
                ->label('PDF File')
                ->acceptedFileTypes(['application/pdf'])
                ->directory('price-lists')
                ->visibility('public')
                ->maxSize(10240)
                ->required()
                ->downloadable()
                ->columnSpanFull(),

            TextInput::make('sort_order')
                ->label('Order')
                ->numeric()
                ->default(0)
                ->columnSpan(1),

            Toggle::make('is_active')
                ->default(true)
                ->columnSpan(1),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site.name')
                    ->label('Site')
                    ->sortable(),

                TextColumn::make('title')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('file')
                    ->label('File')
                    ->formatStateUsing(fn($state) => basename($state))
                    ->limit(40)
                    ->color('info'),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y')
                    ->sortable(),
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
            'index'  => Pages\ListPriceListPdfs::route('/'),
            'create' => Pages\CreatePriceListPdf::route('/create'),
            'edit'   => Pages\EditPriceListPdf::route('/{record}/edit'),
        ];
    }
}
