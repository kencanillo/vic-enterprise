<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use HasFactory;
    protected $fillable = ['warehouse_id','product_id','quantity_type','movement_type','quantity','previous_quantity','new_quantity','discrepancy_percentage','flagged_for_review','remarks','reference_type','reference_id','created_by'];
    protected $casts = ['flagged_for_review' => 'boolean'];
    public function warehouse(){ return $this->belongsTo(Warehouse::class); }
    public function product(){ return $this->belongsTo(Product::class); }
    public function creator(){ return $this->belongsTo(User::class, 'created_by'); }
}