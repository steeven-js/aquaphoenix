<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'order_items';

    /**
     * @var array
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'qty',
    ];
}
