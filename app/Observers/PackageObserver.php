<?php

namespace App\Observers;

use App\Models\Package;
use App\Models\StockMovement;

class PackageObserver
{
    public function updated(Package $package)
    {
        if ($package->status === 'Confirmed' && $package->isDirty('status')) {
            StockMovement::create([
                'product_id' => $package->product_id,
                'package_id' => $package->id,
                'type' => 'IN',
                'quantity' => $package->quantity,
                'date' => now(),
                'performed_by' => auth()->id(),
            ]);
        }
    }
}
