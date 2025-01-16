<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modèle pour gérer les statistiques mensuelles
 */
class Month extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Les attributs qui peuvent être assignés en masse
     *
     * @var array<string>
     */
    protected $fillable = [
        'year',             // Année des statistiques
        'month',            // Nom du mois en français
        'month_number',     // Numéro du mois (01-12)
        'count',            // Nombre de commandes livrées
        'report_created_at' // Date de création du rapport
    ];

    /**
     * Les attributs à convertir automatiquement
     *
     * @var array<string, string>
     */
    protected $casts = [
        'count' => 'integer',             // Conversion en entier
        'report_created_at' => 'datetime', // Conversion en datetime
    ];
}
