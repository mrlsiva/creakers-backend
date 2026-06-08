<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FestivalOfferResource\Pages;
use App\Models\FestivalOffer;
use App\Models\Site;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FestivalOfferResource extends Resource
{
    protected static ?string $model = FestivalOffer::class;
    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?string $navigationLabel = 'Festival Offer';
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('site_id')
                ->label('Site')
                ->options(Site::where('is_active', true)->pluck('name', 'id'))
                ->required()
                ->unique(ignoreRecord: true)
                ->columnSpanFull(),

            Section::make('Offer')->schema([
                Toggle::make('is_active')
                    ->label('Enabled')
                    ->helperText('Show this offer section on the site.')
                    ->default(false)
                    ->columnSpanFull(),

                TextInput::make('title')
                    ->placeholder('e.g. Diwali Festival Special Offer')
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('sub_title')
                    ->label('Sub Title')
                    ->placeholder('e.g. Exclusive deals ending soon. Hurry up!')
                    ->maxLength(255)
                    ->columnSpanFull(),

                DateTimePicker::make('ends_at')
                    ->label('Countdown Ends At')
                    ->helperText('The countdown timer counts down to this date and time.')
                    ->seconds(false)
                    ->columnSpan(1),
            ])->columns(2),

            Section::make('Button')->schema([
                TextInput::make('button_label')
                    ->label('Label')
                    ->placeholder('e.g. Shop Festival Offers')
                    ->maxLength(255)
                    ->columnSpan(1),

                TextInput::make('button_url')
                    ->label('URL')
                    ->placeholder('https://...')
                    ->maxLength(255)
                    ->columnSpan(1),

                Toggle::make('button_open_in_new_tab')
                    ->label('Open in new window')
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site.name')
                    ->label('Site')
                    ->sortable(),

                TextColumn::make('title')
                    ->limit(40),

                TextColumn::make('ends_at')
                    ->label('Countdown Ends At')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Enabled')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListFestivalOffers::route('/'),
            'create' => Pages\CreateFestivalOffer::route('/create'),
            'edit'   => Pages\EditFestivalOffer::route('/{record}/edit'),
        ];
    }
}
