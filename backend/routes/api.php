<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SampleController;
use App\Http\Controllers\Api\SystemController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Report Integrations API routes
|--------------------------------------------------------------------------
| All routes are prefixed with /api automatically by Laravel's
| bootstrap/app.php. The React frontend (frontend/) talks to these.
*/

Route::get('/dashboard/summary', [DashboardController::class, 'summary']);

Route::get('/system/about', [SystemController::class, 'about'])->name('system.about');
Route::get('/system/health', [SystemController::class, 'health'])->name('system.health');

// Ready-made sample reports, one or more per generator.
Route::prefix('samples')->name('samples.')->group(function () {
    Route::get('/', [SampleController::class, 'index'])->name('index');
    Route::get('/{id}/preview', [SampleController::class, 'preview'])->name('preview');
    Route::post('/{id}/generate', [SampleController::class, 'generate'])->name('generate');
});

Route::prefix('reports')->name('reports.')->group(function () {
    // History / listing - reads the `reports` table.
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/types', [ReportController::class, 'types'])->name('types');

    Route::post('/pdf', [ReportController::class, 'generatePdf'])->name('pdf');
    Route::post('/excel', [ReportController::class, 'generateExcel'])->name('excel');
    Route::post('/csv', [ReportController::class, 'generateCsv'])->name('csv');
    Route::post('/docx', [ReportController::class, 'generateDocx'])->name('docx');

    Route::delete('/{report}', [ReportController::class, 'destroy'])->whereNumber('report')->name('destroy');
    Route::get('/download/{filename}', [ReportController::class, 'download'])->name('download');
});
