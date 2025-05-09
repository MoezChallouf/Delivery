<?php
// app/Models/Product.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = ['code_article', 'size', 'color', 'quality'];

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    public function stockMovements()
{
    return $this->hasMany(StockMovement::class);
}
}
