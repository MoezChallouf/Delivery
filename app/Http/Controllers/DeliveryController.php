<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
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
            'client_name' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'packages' => 'required|array|min:1'
        ]);

        DB::transaction(function () use ($request) {
            $delivery = Delivery::create($request->only('client_name', 'delivery_date'));
            
            Package::whereIn('id', $request->packages)
                ->update(['status' => 'delivered']);
            
            $delivery->packages()->attach($request->packages);
        });

        return response()->json(['message' => 'Delivery created successfully']);
    }
}
