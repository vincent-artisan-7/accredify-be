<?php

use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

Route::post('/verify-json', [VerificationController::class, 'verifyJson'])->name('verify-json');
