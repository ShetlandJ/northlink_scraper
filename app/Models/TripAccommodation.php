<?php

namespace App\Models;

use App\Models\Trip;
use App\Models\TripPrice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripAccommodation extends Model
{
    protected $table = 'trip_accommodations';

    public const LERWICK_TO_ABERDEEN = 'LEAB';
    public const ABERDEEN_TO_LERWICK = 'ABLE';

    protected $fillable = [
        'trip_id',
        'amount',
        'bnBIncluded',
        'capacity',
        'description',
        'extrasIncluded',
        'hasSeaView',
        'identifier',
        'isAccessibleCabin',
        'price',
        'resourceCode',
        'sleeps',
    ];

    protected $casts = [
        'trip_id' => 'integer',
        'amount' => 'integer',
        'bnBIncluded' => 'boolean',
        'capacity' => 'integer',
        'description' => 'string',
        'extrasIncluded' => 'boolean',
        'hasSeaView' => 'boolean',
        'identifier' => 'string',
        'isAccessibleCabin' => 'boolean',
        'price' => 'integer',
        'resourceCode' => 'string',
        'sleeps' => 'integer',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }
}