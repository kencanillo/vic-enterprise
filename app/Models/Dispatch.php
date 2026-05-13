<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispatch extends Model
{
    use HasFactory;

    protected $fillable = ['sales_order_id','dr_number','dispatch_date','status','customer_id','pod_location','hauler_id','vehicle_id','driver_id','balance','operational_area_id','transport_mode','notes','created_by','delivered_at','cancelled_at'];
    protected $casts = ['dispatch_date' => 'date', 'delivered_at' => 'datetime', 'cancelled_at' => 'datetime'];

    public function salesOrder(){ return $this->belongsTo(SalesOrder::class); }
    public function customer(){ return $this->belongsTo(Customer::class); }
    public function hauler(){ return $this->belongsTo(Hauler::class); }
    public function vehicle(){ return $this->belongsTo(Vehicle::class); }
    public function driver(){ return $this->belongsTo(Driver::class); }
    public function operationalArea(){ return $this->belongsTo(OperationalArea::class); }
    public function items(){ return $this->hasMany(DispatchItem::class); }
}