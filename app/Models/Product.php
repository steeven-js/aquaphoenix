<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle pour gérer les produits
 */
class Product extends Model
{
    use HasFactory;

    /**
     * Les attributs qui peuvent être assignés en masse
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',          // Nom du produit
        'slug',          // Slug pour l'URL
        'description',   // Description du produit
        'is_visible',    // Visibilité du produit
        'published_at',  // Date de publication
    ];

    /**
     * Les attributs à convertir automatiquement
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_visible' => 'boolean',    // Conversion en booléen
        'published_at' => 'date',     // Conversion en date
    ];

    /**
     * Récupère les articles de commande associés au produit
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
