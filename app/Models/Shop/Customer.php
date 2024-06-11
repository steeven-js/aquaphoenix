<?php

namespace App\Models\Shop;

use App\Models\Address;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'customers';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'address',
        'photo',
        'phone1',
        'phone2',
        'code',
        'commune',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }
}
