<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryStock extends Model
{
    use HasFactory;
    protected $fillable = ['warehouse_id','product_id','palletized_quantity','sling_quantity','tonner_quantity'];
    public function warehouse(){ return $this->belongsTo(Warehouse::class); }
    public function product(){ return $this->belongsTo(Product::class); }
    public function total(): float { return (float) ($this->palletized_quantity + $this->sling_quantity + $this->tonner_quantity); }
}