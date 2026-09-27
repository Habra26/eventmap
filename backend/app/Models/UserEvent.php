<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEvent extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'date',
        'time',
        'venue',
        'address',
        'city',
        'latitude',
        'longitude',
        'category',
        'image',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}