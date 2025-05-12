<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\DeliveryItems;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    public function scanPackage(Request $request)
    {
        $request->validate(['qr_code' => 'required|string']);
        
        $package = Package::where('qr_code', $request->qr_code)
            ->where('status', '!=', 'delivered')
            ->firstOrFail();

        return response()->json([
            'id' => $package->id,
            'product_code' => $package->product->code,
            'quantity' => $package->quantity,
            'batch_number' => $package->batch_number,
            'status' => 'scanned'
        ]);
    }

    public function removePackage(Package $package)
    {
        if ($package->status === 'delivered') {
            abort(400, 'Cannot remove delivered package');
        }
        
        return response()->json(['message' => 'Package removed']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_name' => 'required|string',
            'car_number' => 'required|string',
            'delivery_date' => 'required|date',
            'packages' => 'required|array'
        ]);

        $delivery = Delivery::create([
            'client_name' => $request->client_name,
            'car_number' => $request->car_number,
            'delivery_date' => $request->delivery_date,
            'status' => 'Draft',
            'delivered_by' => auth()->id()
        ]);

        foreach ($request->packages as $qrCode) {
            $package = Package::where('qr_code', $qrCode)->first();
            if ($package) {
                DeliveryItems::create([
                    'delivery_id' => $delivery->id,
                    'package_id' => $package->id,
                    'product_id' => $package->product_id,
                    'quantity' => $package->quantity
                ]);
            }
        }

        return response()->json(['message' => 'Delivery created successfully.'], 201);
    }


    public function show($qrCode)
{
    $package = Package::where('qr_code', $qrCode)
                      ->with('product')
                      ->firstOrFail();

    return response()->json($package);
}
}
