<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Package extends Model
{
    protected $fillable = ['product_id', 'quantity', 'status'];
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->qr_code = Str::uuid();
            $model->created_by = auth()->id();
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
