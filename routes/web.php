<?php

use App\Http\Controllers\PackageTicketController;
use App\Livewire\ScanDeliveryPage;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/packages/{package}/print', [PackageTicketController::class, 'print'])
    ->name('package.print')
    ->middleware(['auth']);

