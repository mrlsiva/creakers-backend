<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SafetyTipResource\Pages;
use App\Models\SafetyTip;
use App\Models\Site;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SafetyTipResource extends Resource
{
    protected static ?string $model = SafetyTip::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Safety Tips';
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('site_id')
                ->label('Site')
                ->options(Site::where('is_active', true)->pluck('name', 'id'))
                ->required()
                ->columnSpan(1),

            Select::make('type')
                ->options([
                    'image' => 'Infographic Image',
                    'tip'   => 'Safety Tip (Text)',
                ])
                ->default('tip')
                ->required()
                ->live()
                ->columnSpan(1),

            TextInput::make('sort_order')
                ->label('Order')
                ->numeric()
                ->default(0)
                ->columnSpan(1),

            Toggle::make('is_active')
                ->default(true)
                ->columnSpan(1),

            FileUpload::make('image')
                ->label('Infographic Image')
                ->image()
                ->directory('safety-tips')
                ->visibility('public')
                ->imagePreviewHeight('120')
                ->maxSize(2048)
                ->visible(fn(Get $get) => $get('type') === 'image')
                ->columnSpanFull(),

            TextInput::make('title')
                ->label('Tip Title')
                ->maxLength(255)
                ->visible(fn(Get $get) => $get('type') === 'tip')
                ->columnSpanFull(),

            Textarea::make('description')
                ->label('Tip Description')
                ->rows(4)
                ->visible(fn(Get $get) => $get('type') === 'tip')
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state) => $state === 'image' ? 'info' : 'success')
                    ->formatStateUsing(fn(string $state) => $state === 'image' ? 'Image' : 'Tip'),

                ImageColumn::make('image')
                    ->height(48)
                    ->width(72)
                    ->defaultImageUrl(null)
                    ->visible(true),

                TextColumn::make('title')
                    ->limit(45)
                    ->default('—'),

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
                SelectFilter::make('type')
                    ->options(['image' => 'Infographic Image', 'tip' => 'Safety Tip']),
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
            'index'  => Pages\ListSafetyTips::route('/'),
            'create' => Pages\CreateSafetyTip::route('/create'),
            'edit'   => Pages\EditSafetyTip::route('/{record}/edit'),
        ];
    }
}
