<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientLogoResource\Pages;
use App\Models\ClientLogo;
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
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ClientLogoResource extends Resource
{
    protected static ?string $model = ClientLogo::class;
    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'Client Logos';
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('site_id')
                ->label('Site')
                ->options(Site::where('is_active', true)->pluck('name', 'id'))
                ->required()
                ->columnSpan(1),

            TextInput::make('name')
                ->label('Client Name')
                ->placeholder('e.g. Acme Corp')
                ->maxLength(255)
                ->columnSpan(1),

            FileUpload::make('logo')
                ->label('Logo')
                ->image()
                ->directory('client-logos')
                ->visibility('public')
                ->imagePreviewHeight('80')
                ->maxSize(1024)
                ->required()
                ->columnSpanFull(),

            TextInput::make('sort_order')
                ->label('Order')
                ->numeric()
                ->default(0)
                ->helperText('Lower number shows first')
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
                ImageColumn::make('logo')
                    ->height(48)
                    ->width(80),

                TextColumn::make('name')
                    ->label('Client Name')
                    ->default('—')
                    ->searchable(),

                TextColumn::make('site.name')
                    ->label('Site')
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Order')
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
            'index'  => Pages\ListClientLogos::route('/'),
            'create' => Pages\CreateClientLogo::route('/create'),
            'edit'   => Pages\EditClientLogo::route('/{record}/edit'),
        ];
    }
}
