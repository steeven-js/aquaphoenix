<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Modèle pour gérer les utilisateurs
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Les attributs qui peuvent être assignés en masse
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',      // Nom de l'utilisateur
        'email',     // Email de l'utilisateur
        'password',  // Mot de passe de l'utilisateur
    ];

    /**
     * Les attributs qui doivent être cachés pour la sérialisation
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',        // Mot de passe
        'remember_token',  // Token de connexion persistante
    ];

    /**
     * Récupère les attributs qui doivent être convertis
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',  // Date de vérification de l'email
            'password' => 'hashed',             // Mot de passe hashé
        ];
    }
}
