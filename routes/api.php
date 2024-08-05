<?php

use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->post('/verify-json', [VerificationController::class, 'verifyJson'])->name('verify-json');
// Route::post('/verify-json', [VerificationController::class, 'verifyJson'])->name('verify-json');
