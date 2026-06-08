<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Représente un événement stocké localement en DB
// Les événements ne sont persistés qu'au moment où un utilisateur les ajoute en favori
class Event extends Model
{
    protected $fillable = [
        'ticketmaster_id', // Identifiant externe stable provenant de l'API Ticketmaster
        'title',
        'date',
        'city',
        'latitude',
        'longitude',
        'image_url',
        'ticket_url',
        'source',          // prévu pour d'autres sources futures
    ];

    // Un événement peut être mis en favori par plusieurs utilisateurs
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }
}
