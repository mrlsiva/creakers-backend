<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Exports\ProductsExport;
use App\Filament\Resources\ProductResource;
use App\Imports\ProductsImport;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Actions as FormActions;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('Download CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(fn() => Excel::download(
                    new ProductsExport(),
                    'products_' . now()->format('Y-m-d') . '.csv',
                    \Maatwebsite\Excel\Excel::CSV
                )),

            Action::make('upload')
                ->label('Upload CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('info')
                ->form([
                    FormActions::make([
                        FormAction::make('download_template')
                            ->label('Download Sample Template')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('gray')
                            ->url(asset('templates/products-template.csv'))
                            ->openUrlInNewTab(),
                    ])->fullWidth(),

                    FileUpload::make('file')
                        ->label('CSV / Excel File')
                        ->disk('local')
                        ->directory('imports')
                        ->acceptedFileTypes([
                            'text/csv',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->required()
                        ->helperText('Columns: Category, Name, Slug (optional), Per, Description, Sort Order, Is Active (Yes/No), All Sites (Yes/No), Site, MRP, Discount Type (percentage/flat), Discount Value, Our Price'),
                ])
                ->action(function (array $data) {
                    try {
                        Excel::import(new ProductsImport(), Storage::disk('local')->path($data['file']));
                        Notification::make()->title('Products imported successfully')->success()->send();
                    } catch (\Throwable $e) {
                        Notification::make()->title('Import failed: ' . $e->getMessage())->danger()->send();
                    }
                }),

            CreateAction::make(),
        ];
    }
}
