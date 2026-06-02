<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Exports\CategoriesExport;
use App\Filament\Resources\CategoryResource;
use App\Imports\CategoriesImport;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Actions as FormActions;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('Download CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(fn() => Excel::download(
                    new CategoriesExport(),
                    'categories_' . now()->format('Y-m-d') . '.csv',
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
                            ->url(asset('templates/categories-template.csv'))
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
                        ->helperText('Columns: Name, Slug (optional), Sort Order, Is Active (Yes/No)'),
                ])
                ->action(function (array $data) {
                    try {
                        Excel::import(new CategoriesImport(), storage_path('app/' . $data['file']));
                        Notification::make()->title('Categories imported successfully')->success()->send();
                    } catch (\Throwable $e) {
                        Notification::make()->title('Import failed: ' . $e->getMessage())->danger()->send();
                    }
                }),

            CreateAction::make(),
        ];
    }
}
