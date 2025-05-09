<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Delivery extends Model
{
    protected $fillable = [
        'client_name', 'car_number', 'delivery_date', 
        'status', 'delivered_by'
    ];

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'delivery_items')
            ->withPivot('quantity');
    }
}