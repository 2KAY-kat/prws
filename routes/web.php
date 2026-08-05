<?php

use App\Http\Controllers\AuditController;

Route::get('/health', fn () => response('ok', 200));

Route::get('/', [AuditController::class, 'create'])->name('audits.create');
Route::get('/audits/{audit}', [AuditController::class, 'show'])->name('audits.show');
Route::get('/audits', [AuditController::class, 'index'])->name('audits.index');

Route::middleware('throttle:10,1')->group(function () {
    Route::post('/audits', [AuditController::class, 'store'])->name('audits.store');
    Route::post('/audits/{audit}/rescan', [AuditController::class, 'rescan'])->name('audits.rescan');
});
