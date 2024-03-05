<?php

use Inertia\Inertia;
use App\Models\TeslaAccount;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\TeslaAccountController;
use App\Http\Controllers\VehicleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::middleware(['tesla.api.linked'])->group(function() {
    
        Route::get('/dashboard', function () {
            return Inertia::render('Dashboard');
        })->name('dashboard');

        Route::resource('vehicles', VehicleController::class);
    });
    Route::post('tesla-accounts/{provider}/get-vehicles', [TeslaAccountController::class, 'getVehicles'])->name('tesla-accounts.get-vehicles');
    Route::get('/tesla-accounts', [TeslaAccountController::class, 'index'])->name('tesla-accounts.index');
    Route::get('/tesla-accounts/{provider}', [TeslaAccountController::class, 'linkForm'])->name('tesla-accounts.link-form');
    Route::post('/tesla-accounts/{provider}/link', [TeslaAccountController::class, 'link'])->name('tesla-accounts.link');
    Route::post('/tesla-accounts/{provider}/unlink', [TeslaAccountController::class, 'unlink'])->name('tesla-accounts.unlink');


});
