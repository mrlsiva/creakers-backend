<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\ItemsRelationManager;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('status')
                ->options(Order::statuses())
                ->required(),

Textarea::make('notes')->rows(2)->columnSpanFull(),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            // ── Enquiry header ──
            Section::make()->schema([
                Grid::make(3)->schema([
                    TextEntry::make('order_number')
                        ->label('Enquiry No')
                        ->weight(FontWeight::Bold)
                        ->size(TextEntry\TextEntrySize::Large),

                    TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->color(fn($state) => Order::statusColor($state)),

                    TextEntry::make('created_at')
                        ->label('Date')
                        ->date('d/m/Y'),
                ]),
            ]),

            // ── From / To ──
            Grid::make(2)->schema([
                Section::make('From')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('site.name')
                            ->label('')
                            ->weight(FontWeight::Bold)
                            ->size(TextEntry\TextEntrySize::Large)
                            ->color('danger'),
                        TextEntry::make('site.address')->label('Address')->default('—'),
                        TextEntry::make('site.phone')->label('Phone')->default('—'),
                        TextEntry::make('site.admin_email')->label('Email')->default('—'),
                    ]),

                Section::make('To')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('customer_name')
                            ->label('')
                            ->weight(FontWeight::Bold)
                            ->size(TextEntry\TextEntrySize::Large),
                        TextEntry::make('customer_full_address')
                            ->label('Address')
                            ->state(fn($record) => implode(', ', array_filter([
                                $record->customer_address,
                                $record->customer_city,
                                $record->customer_district,
                                $record->customer_state,
                            ])) . ($record->customer_pincode ? ' - ' . $record->customer_pincode : '') ?: '—'),
                        TextEntry::make('customer_phone')->label('Phone')->default('—'),
                        TextEntry::make('customer_email')->label('Email')->default('—'),
                    ]),
            ]),

            // ── Items table ──
            Section::make('Order Items')->schema([
                RepeatableEntry::make('items')
                    ->label('')
                    ->schema([
                        TextEntry::make('product_id')->label('Code'),
                        TextEntry::make('product_name')->label('Product'),
                        TextEntry::make('category_name')->label('Category')->default('—'),
                        TextEntry::make('mrp')->label('MRP')->money('INR'),
                        TextEntry::make('disc_pct')
                            ->label('Disc %')
                            ->state(fn($record) => $record->mrp > 0
                                ? round((($record->mrp - $record->our_price) / $record->mrp) * 100) . '%'
                                : '0%'),
                        TextEntry::make('our_price')->label('Our Price')->money('INR'),
                        TextEntry::make('quantity')->label('Qty'),
                        TextEntry::make('subtotal')->label('Total')->money('INR'),
                    ])
                    ->columns(8),
            ]),

            // ── Summary ──
            Section::make()->schema([
                Grid::make(2)->schema([
                    TextEntry::make('notes')->label('Notes')->default('—')->columnSpan(1),
                    TextEntry::make('total_amount')
                        ->label('Payable Amount')
                        ->money('INR')
                        ->weight(FontWeight::Bold)
                        ->size(TextEntry\TextEntrySize::Large)
                        ->color('success')
                        ->columnSpan(1)
                        ->alignEnd(),
                ]),
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

                TextColumn::make('order_number')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('site.name')
                    ->label('Site')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('customer_name')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('customer_phone')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('customer_email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total_amount')
                    ->money('INR')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->toggleable()
                    ->color(fn(string $state) => Order::statusColor($state)),

                TextColumn::make('created_at')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('date_range')
                    ->label('Order Date')
                    ->form([
                        DatePicker::make('from')
                            ->label('From Date')
                            ->native(false)
                            ->displayFormat('d M Y'),
                        DatePicker::make('to')
                            ->label('To Date')
                            ->native(false)
                            ->displayFormat('d M Y'),
                    ])
                    ->columns(2)
                    ->columnSpan(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['to'],   fn($q) => $q->whereDate('created_at', '<=', $data['to']));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from']) {
                            $indicators[] = 'From: ' . Carbon::parse($data['from'])->format('d M Y');
                        }
                        if ($data['to']) {
                            $indicators[] = 'To: ' . Carbon::parse($data['to'])->format('d M Y');
                        }
                        return $indicators;
                    }),

                SelectFilter::make('status')
                    ->options(Order::statuses())
                    ->columnSpan(1),

                SelectFilter::make('site')
                    ->relationship('site', 'name')
                    ->columnSpan(1),
            ])
            ->filtersFormColumns(2)
            ->actions([
                ViewAction::make()
                    ->icon('heroicon-o-eye')
                    ->tooltip('View'),
                EditAction::make()
                    ->icon('heroicon-s-pencil-square')
                    ->tooltip('Update Status'),
            ]); 
    }

    public static function getRelationManagers(): array
    {
        return [ItemsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
