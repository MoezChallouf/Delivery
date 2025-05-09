<?php

namespace App\Filament\Resources\DeliveryResource\Pages;

use App\Filament\Resources\DeliveryResource;
use App\Models\Delivery;
use App\Models\Package;
use App\Models\StockMovement;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;

class DeliveryPage extends Page
{
    protected static string $resource = DeliveryResource::class;

    protected static string $view = 'filament.resources.delivery-resource.pages.delivery-page';

    public $client_name;
    public $car_number;
    public $delivery_date;
    public $scannedPackages = [];

    protected $rules = [
        'client_name' => 'required|string|max:255',
        'car_number' => 'required|string|max:255',
        'delivery_date' => 'required|date',
        'scannedPackages' => 'required|array|min:1',
    ];

    public function qrScanned($qrCode)
    {
        $package = Package::with('product')
            ->where('qr_code', $qrCode)
            ->where('status', 'Confirmed')
            ->first();

        if (!$package) {
            return;
        }

        if (collect($this->scannedPackages)->contains('id', $package->id)) {
            $this->dispatch('duplicatePackage', packageId: $package->id);
            return;
        }

        $this->scannedPackages[] = [
            'id' => $package->id,
            'product_id' => $package->product_id,
            'product_code' => $package->product->code_article,
            'quantity' => $package->quantity
        ];
    }

    public function submit()
    {
        $this->validate();

        DB::transaction(function () {
            $delivery = Delivery::create([
                'client_name' => $this->client_name,
                'car_number' => $this->car_number,
                'delivery_date' => $this->delivery_date,
                'status' => 'Completed',
                'delivered_by' => auth()->id(),
            ]);

            foreach ($this->scannedPackages as $package) {
                // Create delivery item
                $delivery->items()->create([
                    'package_id' => $package['id'],
                    'product_id' => $package['product_id'],
                    'quantity' => $package['quantity'],
                ]);

                // Update package status
                Package::where('id', $package['id'])->update(['status' => 'Delivered']);

                // Create stock movement
                StockMovement::create([
                    'product_id' => $package['product_id'],
                    'package_id' => $package['id'],
                    'type' => 'OUT',
                    'quantity' => $package['quantity'],
                    'date' => now(),
                    'performed_by' => auth()->id(),
                ]);
            }
        });

        return redirect()->route('filament.admin.resources.deliveries.index')
            ->with('success', 'Delivery created successfully!');
    }
}
