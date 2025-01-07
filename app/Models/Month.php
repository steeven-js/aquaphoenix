<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Month extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'year',
        'month',
        'month_number',
        'count',
        'report_created_at',
    ];

    protected $casts = [
        'count' => 'integer',
        'report_created_at' => 'datetime',
    ];
}
