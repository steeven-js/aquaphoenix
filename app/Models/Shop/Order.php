<?php

namespace App\Models\Shop;

use App\Models\Shop\OrderItem;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'orders';

    /**
     * @var array<int, string>
     */

    protected $fillable = [
        'customer_id',
        'number',
        'status',
        'notes',
        'published_at',
        'delivered_date',
        'url',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function getFormattedPublishedDate(): string
    {
        return $this->published_at
            ? Carbon::parse($this->published_at)->isoFormat('D MMMM YYYY')
            : '';
    }

    public function getFormattedDeliveredDate(): string
    {
        return $this->delivered_date
            ? Carbon::parse($this->delivered_date)->isoFormat('D MMMM YYYY')
            : '';
    }
}
