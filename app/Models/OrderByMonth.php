<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderByMonth extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'month',
        'month_id',
        'order_id',
    ];

    public function month()
    {
        return $this->belongsTo(Month::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
