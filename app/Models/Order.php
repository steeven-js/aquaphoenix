<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle pour gérer les commandes
 */
class Order extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Les attributs qui peuvent être assignés en masse
     *
     * @var array<string>
     */
    protected $fillable = [
        'customer_id',              // ID du client
        'number',                   // Numéro de commande
        'status',                   // Statut de la commande
        'notes',                    // Notes sur la commande
        'published_at',             // Date de publication
        'delivered_date',           // Date de livraison
        'url',                      // URL du bon de livraison
        'report_delivered',         // Indicateur si le rapport a été livré
        'report_delivered_date',    // Date de livraison du rapport
    ];

    /**
     * Les attributs à convertir automatiquement
     *
     * @var array<string, string>
     */
    protected $casts = [
        'published_at' => 'date',
        'delivered_date' => 'date',
        'report_delivered' => 'boolean',
        'report_delivered_date' => 'date',
        'deleted_at' => 'datetime',
    ];

    /**
     * Récupère le client associé à la commande
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Récupère les articles de la commande
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
