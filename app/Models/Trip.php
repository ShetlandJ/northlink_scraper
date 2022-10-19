<?php

namespace App\Models;

use App\Models\TripPrice;
use App\Models\TripAccommodation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    protected $table = 'trips';

    public const LERWICK_TO_ABERDEEN = 'LEAB';
    public const ABERDEEN_TO_LERWICK = 'ABLE';

    protected $fillable = [
        'date',
        'price',
        'bookable',
        'noAccommodationsAvailable',
        'noVehicleCapacity',
        'noPassengerCapacity',
        'routeCode',
        'departFrom',
        'returnFrom',
    ];

    protected $casts = [
        'capacity',
    ];

    public function prices(): HasMany
    {
        return $this->hasMany(TripPrice::class);
    }

    public function accomodation(): HasMany
    {
        return $this->hasMany(TripAccommodation::class);
    }

    public function getCapacityAttribute(): int
    {
        $tripPrice = $this->prices->where('resourceCode', 'PAX')->first();
        return $tripPrice ? $tripPrice->capacity : 0;
    }

    public function scopeWithPrices(Builder $query): Builder
    {
        return $query->with('prices');
    }
}