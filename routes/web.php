<?php

use App\Http\Controllers\EnquiryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin/login');
});
Route::get('/enquiry/{orderNumber}/download', [EnquiryController::class, 'download'])
    ->middleware('auth')
    ->name('enquiry.download');

Route::get('/clear', function() {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    return "Cleared!!!";
});