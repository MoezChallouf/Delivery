<?php

namespace App\Filament\Resources\DeliveryResource\Pages;

use App\Filament\Resources\DeliveryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeliveries extends ListRecords
{
    protected static string $resource = DeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Make Delivery')
            ->label('Delivery')
            ->color('primary')
            ->icon('heroicon-o-truck')
            ->url('/admin/deliveries/deliverypage'),
        ];
    }
}
