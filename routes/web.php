<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Route for Ziggy (if needed for routing)
// Route::get('/ziggy', function () {
//     return response()->json(new Ziggy);
// });

// Welcome page
Route::get('/', function () {
    // Redirect to the login page if the user is not authenticated
    if (auth()->check()) {
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    } else {
        return redirect()->route('login');
    }
})->name('home');

// Dashboard page
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Route for home page (JSON upload page)
Route::middleware('auth')->get('/json-upload', function () {
    return Inertia::render('AccredifyUpload');
})->name('json-upload');

require __DIR__ . '/auth.php';
