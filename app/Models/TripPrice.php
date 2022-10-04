<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripPrice extends Model
{
    protected $table = 'trip_prices';

    protected $fillable = [
        'trip_id',
        'resourceCode',
        'price',
        'ticketType',
        'type',
        'available',
        'yieldClass',
        'capacity',
        'intervalValue',
        'resourceType',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }
}