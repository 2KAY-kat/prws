<?php

use App\Http\Controllers\AuditController;

Route::get('/', [AuditController::class, 'create'])->name('audits.create');
Route::get('/audits/{audit}', [AuditController::class, 'show'])->name('audits.show');
Route::get('/audits', [AuditController::class, 'index'])->name('audits.index');

Route::post('/audits', [AuditController::class, 'store'])->name('audits.store');