<?php

use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\PackageTicketController;
use App\Livewire\ScanDeliveryPage;
use App\Models\Package;
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

    Route::get('/packages/{qr_code}', function ($qrCode) {
        $package = Package::with('product')
                    ->where('qr_code', $qrCode)
                    ->firstOrFail();
                    
        return response()->json([
            'qr_code' => $package->qr_code,
            'product' => [
                'code_article' => $package->product->code_article,
                'size' => $package->product->size,
                'color' => $package->product->color
            ],
            'quantity' => $package->quantity,
            'status' => $package->status,
            'created_at' => $package->created_at->format('Y-m-d H:i')
        ]);
    })->missing(function () {
        return response()->json(['message' => 'Package not found'], 404);
    });


Route::prefix('admin')->group(function () {
    Route::post('/deliveries', [DeliveryController::class, 'store'])->name('deliveries.store');
    Route::get('/packages/{qrCode}', [DeliveryController::class, 'show'])->name('show');
});