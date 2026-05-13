<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DispatchItem extends Model
{
    use HasFactory;
    protected $fillable = ['dispatch_id','sales_order_item_id','product_id','quantity'];
    public function product(){ return $this->belongsTo(Product::class); }
    public function salesOrderItem(){ return $this->belongsTo(SalesOrderItem::class); }
}