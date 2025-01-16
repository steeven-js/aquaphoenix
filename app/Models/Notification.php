<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle pour gérer les notifications
 */
class Notification extends Model
{
    /**
     * Les attributs qui peuvent être assignés en masse
     *
     * @var array<string>
     */
    protected $fillable = [
        'type',             // Type de notification
        'notifiable_type',  // Type du modèle notifiable
        'notifiable_id',    // ID du modèle notifiable
        'data',             // Données de la notification
        'read_at'           // Date de lecture
    ];

    /**
     * Les attributs à convertir automatiquement
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',      // Conversion en tableau
        'read_at' => 'datetime', // Conversion en datetime
    ];
}
