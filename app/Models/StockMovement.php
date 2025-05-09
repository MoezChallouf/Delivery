<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id', 'package_id', 'type', 
        'quantity', 'date', 'performed_by'
    ];

    public function product()
{
    return $this->belongsTo(Product::class);
}

public function package()
{
    return $this->belongsTo(Package::class);
}
}