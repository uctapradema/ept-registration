<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Mahasiswa\DashboardController;
use App\Http\Controllers\Mahasiswa\ScheduleController;
use App\Http\Controllers\Mahasiswa\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Mahasiswa Routes
Route::middleware(['auth', 'mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Schedules
    Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    
    // Registrations
    Route::get('/registrations', [RegistrationController::class, 'index'])->name('registrations.index');
    Route::get('/registrations/create/{schedule}', [RegistrationController::class, 'create'])->name('registrations.create');
    Route::post('/registrations', [RegistrationController::class, 'store'])->name('registrations.store');
    Route::get('/registrations/{registration}', [RegistrationController::class, 'show'])->name('registrations.show');
    Route::get('/registrations/{registration}/payment', [RegistrationController::class, 'uploadPayment'])->name('registrations.payment');
    Route::post('/registrations/{registration}/payment', [RegistrationController::class, 'storePayment'])->name('registrations.payment.store');
    Route::get('/registrations/{registration}/card', [RegistrationController::class, 'card'])->name('registrations.card');
    Route::delete('/registrations/{registration}', [RegistrationController::class, 'cancel'])->name('registrations.cancel');
});

require __DIR__.'/auth.php';
