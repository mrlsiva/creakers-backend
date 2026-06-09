<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers\PricesRelationManager;
use App\Models\Product;
use App\Models\Site;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?int $navigationSort = 3;

    private static function recalc(float $mrp, ?string $type, float $discount, callable $set): void
    {
        if ($mrp <= 0) return;

        $ourPrice = $type === 'flat'
            ? max(0, $mrp - $discount)
            : round($mrp * (1 - $discount / 100), 2);

        $set('our_price', $ourPrice);
    }

    private static function buildSiteRows(array $selectedIds, array $existingRows = []): array
    {
        $existingById = collect($existingRows)->keyBy('site_id');

        return Site::where('is_active', true)
            ->whereIn('id', $selectedIds)
            ->get()
            ->map(function ($site) use ($existingById) {
                if ($existingById->has($site->id)) {
                    $row = $existingById->get($site->id);
                    $row['site_name'] = $site->name;
                    return $row;
                }
                return [
                    'site_id'        => $site->id,
                    'site_name'      => $site->name,
                    'mrp'            => null,
                    'discount_type'  => 'percentage',
                    'discount_value' => 0,
                    'our_price'      => null,
                ];
            })
            ->toArray();
    }

    public static function form(Form $form): Form
    {
        return $form->columns(4)->schema([
            // Row 1: Category | Name | Per  — 3 equal columns, full width
            Grid::make(3)
                ->schema([
                    Select::make('category_id')
                        ->label('Category')
                        ->relationship('category', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),

                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set, $record) {
                            if (!$record) {
                                $set('slug', Str::slug($state));
                            }
                        }),

                    Select::make('per')
                        ->label('Per')
                        ->options(fn() => Product::whereNotNull('per')
                            ->where('per', '!=', '')
                            ->orderBy('per')
                            ->distinct()
                            ->pluck('per', 'per')
                        )
                        ->searchable()
                        ->createOptionForm([
                            TextInput::make('per')
                                ->label('New Per Value')
                                ->placeholder('e.g. 1 Pkt, 1 Bag, 100 Pcs')
                                ->required(),
                        ])
                        ->createOptionUsing(fn(array $data): string => $data['per']),
                ])
                ->columnSpanFull(),

            // Row 2 (edit only): Slug (3 cols) | Sort Order (1 col)
            TextInput::make('slug')
                ->hiddenOn('create')
                ->required()
                ->maxLength(255)
                ->unique(Product::class, 'slug', ignoreRecord: true)
                ->hint('Auto-generated from name. You can edit.')
                ->columnSpan(3),

            TextInput::make('sort_order')
                ->label('Sort Order')
                ->numeric()
                ->default(0)
                ->hiddenOn('create')
                ->columnSpan(1),

            // Row 3: Description (2 cols) | Image (2 cols) — same height
            Textarea::make('description')
                ->rows(3)
                ->extraAttributes(['style' => 'resize:none;'])
                ->columnSpan(2),

            FileUpload::make('image')
                ->image()
                ->disk('public')
                ->directory('products')
                ->imagePreviewHeight('80')
                ->maxSize(2048)
                ->columnSpan(2),

            // Hidden — kept in form state for save, toggled via header button
            Toggle::make('is_active')
                ->default(true)
                ->hidden(),

            Section::make('Pricing & Site Visibility')
                ->columnSpanFull()
                ->schema([
                    CheckboxList::make('selected_sites')
                        ->label('Show on Sites')
                        ->options(fn() => Site::where('is_active', true)->pluck('name', 'id'))
                        ->default(fn() => Site::where('is_active', true)->pluck('id')->toArray())
                        ->columns(3)
                        ->columnSpanFull()
                        ->live()
                        ->dehydrated(false)
                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                            $set('site_prices', self::buildSiteRows(
                                $state ?? [],
                                $get('site_prices') ?? []
                            ));
                        }),

                    Toggle::make('price_all_sites')
                        ->label('Same price for all selected sites')
                        ->default(true)
                        ->live()
                        ->columnSpanFull()
                        ->dehydrated(false),

                    // Single price row — toggle ON
                    Grid::make(4)
                        ->schema([
                            TextInput::make('mrp')
                                ->label('MRP (₹)')
                                ->numeric()
                                ->minValue(0)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn($state, callable $get, callable $set) =>
                                    self::recalc((float) $state, $get('discount_type'), (float) $get('discount_value'), $set))
                                ->dehydrated(false),

                            Select::make('discount_type')
                                ->label('Discount Type')
                                ->options(['percentage' => 'Percentage (%)', 'flat' => 'Flat (₹)'])
                                ->default('percentage')
                                ->live()
                                ->afterStateUpdated(fn($state, callable $get, callable $set) =>
                                    self::recalc((float) $get('mrp'), $state, (float) $get('discount_value'), $set))
                                ->dehydrated(false),

                            TextInput::make('discount_value')
                                ->label(fn(callable $get) => $get('discount_type') === 'flat' ? 'Discount (₹)' : 'Discount (%)')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->maxValue(fn(callable $get) => $get('discount_type') === 'flat'
                                    ? max(0, (float) ($get('mrp') ?: 0))
                                    : 100)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn($state, callable $get, callable $set) =>
                                    self::recalc((float) $get('mrp'), $get('discount_type'), (float) $state, $set))
                                ->dehydrated(false),

                            TextInput::make('our_price')
                                ->label('Our Price (₹)')
                                ->numeric()
                                ->minValue(0)
                                ->dehydrated(false),
                        ])
                        ->visible(fn(callable $get) => (bool) $get('price_all_sites'))
                        ->columnSpanFull(),

                    // Per-site rows — toggle OFF
                    Repeater::make('site_prices')
                        ->label('Price per Site')
                        ->schema([
                            Hidden::make('site_id'),

                            TextInput::make('site_name')
                                ->label('Site')
                                ->readOnly()
                                ->dehydrated(false),

                            TextInput::make('mrp')
                                ->label('MRP (₹)')
                                ->numeric()
                                ->minValue(0)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn($state, callable $get, callable $set) =>
                                    self::recalc((float) $state, $get('discount_type'), (float) $get('discount_value'), $set)),

                            Select::make('discount_type')
                                ->label('Discount Type')
                                ->options(['percentage' => '% Percentage', 'flat' => '₹ Flat'])
                                ->default('percentage')
                                ->live()
                                ->afterStateUpdated(fn($state, callable $get, callable $set) =>
                                    self::recalc((float) $get('mrp'), $state, (float) $get('discount_value'), $set)),

                            TextInput::make('discount_value')
                                ->label(fn(callable $get) => $get('discount_type') === 'flat' ? 'Discount (₹)' : 'Discount (%)')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->maxValue(fn(callable $get) => $get('discount_type') === 'flat'
                                    ? max(0, (float) ($get('mrp') ?: 0))
                                    : 100)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn($state, callable $get, callable $set) =>
                                    self::recalc((float) $get('mrp'), $get('discount_type'), (float) $state, $set)),

                            TextInput::make('our_price')
                                ->label('Our Price (₹)')
                                ->numeric()
                                ->minValue(0),
                        ])
                        ->columns(5)
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->dehydrated(false)
                        ->default(fn() => self::buildSiteRows(
                            Site::where('is_active', true)->pluck('id')->toArray()
                        ))
                        ->visible(fn(callable $get) => !(bool) $get('price_all_sites'))
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')
                    ->label('#')
                    ->rowIndex(),

                ImageColumn::make('image')
                    ->label('Image')
                    ->disk('public')
                    ->size(48)
                    ->defaultImageUrl(asset('images/default-product.svg')),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('per')
                    ->label('Per'),

                TextColumn::make('prices_count')
                    ->counts('prices')
                    ->label('Sites'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                // Toggleable — hidden by default
                TextColumn::make('slug')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('sort_order')
                    ->label('Sort')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('category')->relationship('category', 'name'),
            ])
            ->defaultSort('sort_order')
            ->actions([
                EditAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make()->label('Permanently Delete'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make()->label('Permanently Delete'),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getRelationManagers(): array
    {
        return [PricesRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
