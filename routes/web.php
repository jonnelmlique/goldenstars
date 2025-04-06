<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BarcodeController;

Route::redirect('/', 'app/login');

// Barcode routes
Route::get('/barcode/print/{inventory}', [BarcodeController::class, 'print'])->name('barcode.print');
Route::get('/barcode/print-multiple', [BarcodeController::class, 'printMultiple'])->name('barcode.print-multiple');

// Barcode printing routes
Route::get('/print/barcode/{id}', [App\Http\Controllers\BarcodeController::class, 'print'])->name('print.barcode');
Route::get('/print/barcodes', [App\Http\Controllers\BarcodeController::class, 'printMultiple'])->name('print.barcodes');
