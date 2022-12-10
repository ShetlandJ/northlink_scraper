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

    public const ABERDEEN_TO_KIRKWALL = 'ABKI';
    public const ABERDEEN_TO_LERWICK = 'ABLE';
    public const KIRKWALL_TO_ABERDEEN = 'KIAB';
    public const KIRKWALL_TO_LERWICK = 'KILE';
    public const LERWICK_TO_ABERDEEN = 'LEAB';
    public const LERWICK_TO_KIRKWALL = 'LEKI';
    public const SCRABSTER_TO_STROMNESS = 'SCST';
    public const STROMNESS_TO_SCRABSTER = 'STSC';

    public const ALL_ROUTES = [
        self::ABERDEEN_TO_KIRKWALL,
        self::ABERDEEN_TO_LERWICK,
        self::KIRKWALL_TO_ABERDEEN,
        self::KIRKWALL_TO_LERWICK,
        self::LERWICK_TO_ABERDEEN,
        self::LERWICK_TO_KIRKWALL,
        // self::SCRABSTER_TO_STROMNESS,
        // self::STROMNESS_TO_SCRABSTER,
    ];

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

    public function getCarListingAttribute()
    {
        $tripPrice = $this->prices
            ->where('resourceCode', 'CAR')
            ->where('ticketType', 'STD')
            ->first();

        return $tripPrice;
    }

    public function scopeWithPrices(Builder $query): Builder
    {
        return $query->with('prices');
    }
}
