<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
