<?php

use App\Http\Controllers\BillController;
use App\Http\Controllers\BillingProfileController;
use App\Http\Controllers\DefaultTeamController;
use Inertia\Inertia;
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
        Route::post('vehicles/{vehicle}/unlink', [VehicleController::class, 'unlink'])->name('vehicles.unlink');
        Route::resource('vehicles', VehicleController::class)->except(['create', 'store', 'edit', 'update']);
        
        Route::get('billing-profiles/list', [BillingProfileController::class, 'list'])->name('billing-profiles.list');
        Route::post('billing-profiles/link', [BillingProfileController::class, 'link'])->name('billing-profiles.link');
        // Route::post('billing-profiles/{billing_profile}/unlink', [BillingProfileController::class, 'unlink'])->name('billing-profiles.unlink');
        Route::resource('billing-profiles', BillingProfileController::class);
        
        Route::resource('/bills', BillController::class);
    });
    Route::post('/tesla-account/{provider}/get-vehicles', [TeslaAccountController::class, 'getVehicles'])->name('tesla-accounts.get-vehicles');
    Route::get('/tesla-account', [TeslaAccountController::class, 'index'])->name('tesla-accounts.index');
    Route::get('/tesla-account/{provider}', [TeslaAccountController::class, 'linkForm'])->name('tesla-accounts.link-form');
    Route::post('/tesla-account/{provider}/link', [TeslaAccountController::class, 'link'])->name('tesla-accounts.link');
    Route::post('/tesla-account/{provider}/unlink', [TeslaAccountController::class, 'unlink'])->name('tesla-accounts.unlink');

    Route::post('/teams/{team}/make-default', DefaultTeamController::class)->name('teams.make-default');
});
