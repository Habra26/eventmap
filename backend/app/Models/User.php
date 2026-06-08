<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// Champs autorisés à l'assignation de masse via create() ou update()
#[Fillable(['name', 'email', 'password', 'role'])]
// Ces champs sont exclus de toutes les réponses JSON pour ne jamais exposer le hash du mot de passe
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    // HasApiTokens : ajoute la gestion des tokens Sanctum (createToken, tokens())
    // HasFactory : permet de générer des utilisateurs de test avec UserFactory
    // Notifiable : permet d'envoyer des notifications
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime', // Convertit automatiquement en objet Carbon
            'password' => 'hashed',            // Hache automatiquement le mot de passe lors de l'assignation
        ];
    }

    // Un utilisateur peut avoir plusieurs favoris 
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }
}
