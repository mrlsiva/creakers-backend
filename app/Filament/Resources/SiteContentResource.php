<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteContentResource\Pages;
use App\Models\Site;
use App\Models\SiteContent;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SiteContentResource extends Resource
{
    protected static ?string $model = SiteContent::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Content Pages';
    protected static ?int $navigationSort = 6;
    protected static bool $shouldRegisterNavigation = false;

    /**
     * Allowed content keys. Add new entries here as new content sections are introduced.
     */
    public static function keyOptions(): array
    {
        return [
            'about-us'              => 'About Us',
            'terms-and-conditions'  => 'Terms & Conditions',
            'popup'                 => 'Popup',
            'banner-scrolling-text' => 'Banner Scrolling Text',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('site_id')
                ->label('Site')
                ->options(Site::where('is_active', true)->pluck('name', 'id'))
                ->required()
                ->columnSpan(1),

            Select::make('key')
                ->label('Key')
                ->options(self::keyOptions())
                ->helperText('Used in API URL. Unique per site.')
                ->required()
                ->searchable()
                ->columnSpan(1),

            TextInput::make('title')
                ->required()
                ->maxLength(255)
                ->columnSpan(1),

            TextInput::make('tag')
                ->placeholder('e.g. About Us')
                ->helperText('Small label shown above the title.')
                ->maxLength(255)
                ->columnSpan(1),

            RichEditor::make('body')
                ->label('Content')
                ->toolbarButtons([
                    'heading', 'bold', 'italic', 'underline', 'strike',
                    'bulletList', 'orderedList', 'blockquote',
                    'link', 'h2', 'h3', 'undo', 'redo',
                ])
                ->columnSpanFull(),

            FileUpload::make('image')
                ->image()
                ->directory('site-contents')
                ->visibility('public')
                ->imagePreviewHeight('120')
                ->maxSize(2048)
                ->columnSpan(1),

            Section::make('Features')->schema([
                Repeater::make('features')
                    ->label('')
                    ->schema([
                        TextInput::make('icon')
                            ->placeholder('e.g. star')
                            ->helperText('Icon name used by the frontend.')
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('title')
                            ->placeholder('e.g. Premium Quality')
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('subtitle')
                            ->placeholder('e.g. Certified and tested products')
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(3)
                    ->addActionLabel('Add Feature')
                    ->defaultItems(0)
                    ->collapsible()
                    ->itemLabel(fn(array $state) => $state['title'] ?? 'New Feature')
                    ->columnSpanFull(),
            ])->columnSpanFull(),

            Section::make('Button')->schema([
                TextInput::make('button_label')
                    ->placeholder('e.g. Learn More About Us')
                    ->maxLength(255)
                    ->columnSpan(1),

                TextInput::make('button_url')
                    ->label('URL')
                    ->placeholder('/about')
                    ->maxLength(255)
                    ->columnSpan(1),

                Toggle::make('button_open_in_new_tab')
                    ->label('Open in new window')
                    ->columnSpanFull(),
            ])->columns(2),

            Toggle::make('is_active')
                ->default(true)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site.name')
                    ->label('Site')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('title')
                    ->sortable()
                    ->searchable()
                    ->limit(50),

                TextColumn::make('key')
                    ->label('Key')
                    ->badge()
                    ->color('gray'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('site')
                    ->relationship('site', 'name'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSiteContents::route('/'),
            'create' => Pages\CreateSiteContent::route('/create'),
            'edit'   => Pages\EditSiteContent::route('/{record}/edit'),
        ];
    }
}
