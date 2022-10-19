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

    protected $casts = [
        'available' => 'boolean',
    ];

    public const CODE_TYPES = [
        "NPREM" => "Premium Outside 2 berth cabin",
        "NEXEC" => "Executive Outside 2 berth cabin (bunk beds)",
        "NO2" => "Outside 2 berth cabin",
        "NO3" => "Outside 3 berth cabin (bunk beds)",
        "NI2" => "Inside 2 berth cabin (bunk beds)",
        "NI4" => "Inside 4 berth cabin (bunk beds)",
        "NI4S" => "Inside 4 berth cabin with Privacy Curtains (bunk beds)",
        "NPETPR" => "Premium Outside 2 berth Pet-friendly cabin",
        "NPETI2" => "Inside 2 berth Pet-friendly cabin",
        "NPETO2" => "Outside 2 berth Pet-friendly cabin",
        "NPETI4" => "Inside 4 berth Pet-friendly cabin (bunk beds)",
        "POD" => "Pod in Pod Lounge 1",
        "POD2" => "Pod in Pod Lounge 2",
        "POD3" => "Pod in Pod Lounge 3",
        "NRSEAT" => "Reclining seat",
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }
}