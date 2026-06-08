<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Table de liaison entre un utilisateur et un événement sauvegardé
// Chaque ligne représente un favori
class Favorite extends Model
{
    protected $fillable = [
        'user_id',  // Clé étrangère vers users.id
        'event_id', // Clé étrangère vers events.id (id interne, pas ticketmaster_id)
    ];

    // L'événement associé à ce favori
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    // L'utilisateur propriétaire de ce favori
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
