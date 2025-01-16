<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle pour gérer les informations de l'entreprise
 */
class CompanyInfo extends Model
{
    /**
     * Les attributs qui peuvent être assignés en masse
     *
     * @var array<string>
     */
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

    /**
     * Récupère ou crée les informations par défaut de l'entreprise
     *
     * @return CompanyInfo
     */
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

    /**
     * Accesseur pour obtenir l'adresse complète formatée
     *
     * @return string L'adresse complète au format: adresse, code postal, ville, pays
     */
    public function getFullAddressAttribute()
    {
        return "{$this->address}, {$this->zip_code}, {$this->city}, {$this->country}";
    }
}
