<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    use HasFactory;

    protected $fillable = ['so_number','po_date','customer_id','sales_agent_id','payment_term_id','location','operational_area_id','transport_mode','truck_or_vessel_identifier','status','subtotal','tax_total','grand_total','notes','created_by','approved_by','approved_at'];
    protected $casts = ['po_date' => 'date', 'approved_at' => 'datetime'];

    public function customer(){ return $this->belongsTo(Customer::class); }
    public function salesAgent(){ return $this->belongsTo(SalesAgent::class); }
    public function paymentTerm(){ return $this->belongsTo(PaymentTerm::class); }
    public function operationalArea(){ return $this->belongsTo(OperationalArea::class); }
    public function items(){ return $this->hasMany(SalesOrderItem::class); }
    public function dispatches(){ return $this->hasMany(Dispatch::class); }
    public function creator(){ return $this->belongsTo(User::class, 'created_by'); }

    public function remainingQuantity(): float
    {
        return (float) $this->items->sum(function ($item) { return $item->quantity - $item->dispatched_quantity; });
    }
}