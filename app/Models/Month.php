<?php

namespace App\Models;

use App\Models\Shop\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Month extends Model
{
    use HasFactory;

    protected $table = 'months';

    protected $fillable = [
        'year',
        'month',
        'start_date',
        'end_date',
    ];

    public function orders()
    {
        return $this->hasManyThrough(Order::class, OrderByMonth::class, 'month_id', 'id', 'id', 'order_id');
    }

    public function ordersByMonth()
    {
        return $this->hasMany(OrderByMonth::class);
    }
}
