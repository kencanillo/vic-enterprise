<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['name','hauler_id','plate_number','is_active'];
    public function hauler(){ return $this->belongsTo(Hauler::class); }
}
