<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'ticketmaster_id',
        'title',
        'date',
        'city',
        'latitude',
        'longitude',
        'image_url',
        'ticket_url',
        'source',
    ];

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }
}