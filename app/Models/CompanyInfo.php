<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyInfo extends Model
{
    protected $fillable = [
        'name',
        'address',
        'zip_code',
        'city',
        'country',
        'phone',
        'email',
        'logo',
        'favicon',
    ];

    public static function getDefault()
    {
        return self::firstOrCreate([
            'name' => 'Aquaphoenix',
            'address' => '35 rue Joseph Lagrosilliére',
            'zip_code' => '97220',
            'city' => 'Trinité',
            'country' => 'Martinique',
            'phone' => '+596 696 34 81 12',
            'email' => 'contact@aquaphoenix.fr',
            'logo' => 'images/logo.png',
            'favicon' => 'images/favicon.ico',
        ]);
    }

    public function getFullAddressAttribute()
    {
        return "{$this->address}, {$this->zip_code}, {$this->city}, {$this->country}";
    }
}
