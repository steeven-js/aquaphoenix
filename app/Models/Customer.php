<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle pour gérer les clients
 */
class Customer extends Model
{
    use HasFactory;

    /**
     * Les attributs qui peuvent être assignés en masse
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',      // Nom du client
        'email',     // Email du client
        'address',   // Adresse du client
        'photo',     // Photo du client
        'phone1',    // Numéro de téléphone principal
        'phone2',    // Numéro de téléphone secondaire
        'code',      // Code client
        'commune',   // Commune du client
    ];

    /**
     * Récupère les commandes associées au client
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
