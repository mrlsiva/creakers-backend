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
use Filament\Resources\Resource;
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

            TextInput::make('status_description')
                ->label('Status Track')
                ->disabled()
                ->dehydrated(false)
                ->columnSpanFull(),

            Textarea::make('notes')->rows(2)->columnSpanFull(),
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
                    ->color(fn(string $state) => match ($state) {
                        'pending'    => 'warning',
                        'confirmed'  => 'info',
                        'processing' => 'info',
                        'dispatched' => 'primary',
                        'delivered'  => 'success',
                        'cancelled'  => 'danger',
                        default      => 'gray',
                    }),

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
