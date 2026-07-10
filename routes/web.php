<?php

use App\Http\Controllers\AuditController;

Route::get('/', [AuditController::class, 'create'])->name('audits.create');
Route::post('/audits', [AuditController::class, 'store'])->name('audits.store');
Route::get('/audits/{audit}', [AuditController::class, 'show'])->name('audits.show');