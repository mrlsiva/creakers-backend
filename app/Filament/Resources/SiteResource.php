<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteResource\Pages;
use App\Models\Site;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SiteResource extends Resource
{
    protected static ?string $model = Site::class;
    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),

            TextInput::make('title')
                ->maxLength(255)
                ->placeholder('e.g. Vigo Crackers - Premium Quality Fireworks')
                ->helperText('Shown in the browser tab / page title')
                ->columnSpan(1),

            TextInput::make('slug')
                ->required()
                ->maxLength(255)
                ->unique(Site::class, 'slug', ignoreRecord: true),

            TextInput::make('admin_email')
                ->email()
                ->required()
                ->maxLength(255),

            TextInput::make('phone')
                ->maxLength(100)
                ->placeholder('+91 98765 43210'),

            Textarea::make('address')
                ->rows(2)
                ->columnSpanFull(),

            FileUpload::make('logo')
                ->image()
                ->disk('public')
                ->directory('sites')
                ->imagePreviewHeight('80')
                ->maxSize(2048)
                ->columnSpanFull(),

            FileUpload::make('nav_icon')
                ->label('Nav Icon')
                ->image()
                ->disk('public')
                ->directory('sites/nav-icons')
                ->imagePreviewHeight('48')
                ->maxSize(512)
                ->helperText('Small icon shown in the site navigation / browser tab (favicon)')
                ->columnSpanFull(),

            Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')->label('#')->rowIndex()->toggleable(),
                ImageColumn::make('logo')
                    ->disk('public')
                    ->size(48)
                    ->defaultImageUrl(asset('images/default-product.svg'))
                    ->toggleable(),
                ImageColumn::make('nav_icon')
                    ->label('Nav Icon')
                    ->disk('public')
                    ->size(32)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')->searchable()->sortable()->toggleable(),
                TextColumn::make('title')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('slug')->searchable()->toggleable(),
                TextColumn::make('admin_email')->searchable()->toggleable(),
                IconColumn::make('is_active')->boolean()->label('Active')->toggleable(),
                TextColumn::make('orders_count')
                    ->counts('orders')
                    ->label('Orders')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')->dateTime('d M Y')->sortable()->toggleable(),
            ])
            ->actions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSites::route('/'),
            'create' => Pages\CreateSite::route('/create'),
            'edit' => Pages\EditSite::route('/{record}/edit'),
        ];
    }
}
