<?php

namespace App\Providers;

use App\Filament\Resources\DeliveryResource\Widgets\QrScanner;
use App\Models\Package;
use App\Observers\PackageObserver;
use Filament\Facades\Filament;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Package::observe(PackageObserver::class);
    }
}
