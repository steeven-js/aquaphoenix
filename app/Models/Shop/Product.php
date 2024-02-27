<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'products';

    /**
     * @var array<string, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_visible',
        'published_at',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }
}
