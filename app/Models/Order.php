<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'number',
        'status',
        'notes',
        'published_at',
        'delivered_date',
        'url',
        'report_delivered',
        'report_delivered_date',
    ];

    protected $casts = [
        'published_at' => 'date',
        'delivered_date' => 'date',
        'report_delivered' => 'boolean',
        'report_delivered_date' => 'date',
        'deleted_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
