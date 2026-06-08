<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomeBannerResource\Pages;
use App\Models\HomeBanner;
use App\Models\Site;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HomeBannerResource extends Resource
{
    protected static ?string $model = HomeBanner::class;
    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'Home Banner';
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

            Section::make('Banner')->schema([
                FileUpload::make('image')
                    ->label('Web Banner Image')
                    ->image()
                    ->directory('home-banners')
                    ->visibility('public')
                    ->imagePreviewHeight('120')
                    ->maxSize(2048)
                    ->columnSpan(1),

                FileUpload::make('mobile_image')
                    ->label('Mobile Banner Image')
                    ->image()
                    ->directory('home-banners')
                    ->visibility('public')
                    ->imagePreviewHeight('120')
                    ->maxSize(2048)
                    ->columnSpan(1),

                TextInput::make('top_small_description')
                    ->label('Top Small Description')
                    ->placeholder('e.g. Premium Quality Fireworks Since 1990')
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('title')
                    ->placeholder('e.g. Vigo Crackers')
                    ->maxLength(255)
                    ->columnSpan(1),

                TextInput::make('second_title')
                    ->label('Second Title')
                    ->placeholder('e.g. Light Up Your Celebrations')
                    ->maxLength(255)
                    ->columnSpan(1),

                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
            ])->columns(2),

            Section::make('Buttons')->schema([
                Repeater::make('buttons')
                    ->label('')
                    ->schema([
                        TextInput::make('label')
                            ->placeholder('e.g. Shop Now')
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('url')
                            ->label('URL')
                            ->placeholder('https://...')
                            ->required()
                            ->columnSpan(1),

                        Toggle::make('open_in_new_tab')
                            ->label('Open in new window')
                            ->columnSpan(1),
                    ])
                    ->columns(3)
                    ->maxItems(2)
                    ->addActionLabel('Add Button')
                    ->defaultItems(0)
                    ->collapsible()
                    ->itemLabel(fn(array $state) => $state['label'] ?? 'New Button')
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Web Image')
                    ->height(48)
                    ->width(80),

                ImageColumn::make('mobile_image')
                    ->label('Mobile Image')
                    ->height(48)
                    ->width(80),

                TextColumn::make('site.name')
                    ->label('Site')
                    ->sortable(),

                TextColumn::make('title')
                    ->limit(40),

                TextColumn::make('second_title')
                    ->label('Second Title')
                    ->limit(40),

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
            'index'  => Pages\ListHomeBanners::route('/'),
            'create' => Pages\CreateHomeBanner::route('/create'),
            'edit'   => Pages\EditHomeBanner::route('/{record}/edit'),
        ];
    }
}
