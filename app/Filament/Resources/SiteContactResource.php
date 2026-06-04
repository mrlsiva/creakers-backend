<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteContactResource\Pages;
use App\Models\Site;
use App\Models\SiteContact;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SiteContactResource extends Resource
{
    protected static ?string $model = SiteContact::class;
    protected static ?string $navigationIcon = 'heroicon-o-phone';
    protected static ?string $navigationLabel = 'Contact Info';
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

            Section::make('Contact Details')->schema([
                Textarea::make('address')
                    ->rows(3)
                    ->columnSpan(1),

                TextInput::make('phones')
                    ->label('Phone Numbers')
                    ->placeholder('e.g. +91 80127 99334, 94860 46411')
                    ->helperText('Separate multiple numbers with a comma')
                    ->columnSpan(1),

                TextInput::make('email')
                    ->email()
                    ->columnSpan(1),

                TextInput::make('opening_time')
                    ->placeholder('e.g. All Days : 09.00 AM to 11.00 PM')
                    ->columnSpan(1),
            ])->columns(2),

            Section::make('Social Media')->schema([
                Repeater::make('social_links')
                    ->label('')
                    ->schema([
                        FileUpload::make('icon')
                            ->label('Icon')
                            ->image()
                            ->directory('social-icons')
                            ->visibility('public')
                            ->imagePreviewHeight('48')
                            ->maxSize(512)
                            ->columnSpan(1),

                        TextInput::make('label')
                            ->label('Platform Name')
                            ->placeholder('e.g. Facebook')
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('url')
                            ->label('URL / Number')
                            ->placeholder('https://... or phone number')
                            ->required()
                            ->columnSpan(2),
                    ])
                    ->columns(4)
                    ->addActionLabel('Add Social Link')
                    ->defaultItems(0)
                    ->collapsible()
                    ->itemLabel(fn(array $state) => $state['label'] ?? 'New Link')
                    ->columnSpanFull(),
            ]),

            Section::make('Map')->schema([
                Textarea::make('map_embed_url')
                    ->label('Google Maps Embed URL')
                    ->helperText('Paste the src URL from Google Maps embed iframe')
                    ->rows(2)
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site.name')
                    ->label('Site')
                    ->sortable(),

                TextColumn::make('phones')
                    ->label('Phones')
                    ->limit(40),

                TextColumn::make('email')
                    ->limit(40),

                TextColumn::make('opening_time')
                    ->label('Opening Time')
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
            'index'  => Pages\ListSiteContacts::route('/'),
            'create' => Pages\CreateSiteContact::route('/create'),
            'edit'   => Pages\EditSiteContact::route('/{record}/edit'),
        ];
    }
}
