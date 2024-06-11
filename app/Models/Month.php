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
        'month_number',
        'count',
        'report_created_at',
    ];
}
