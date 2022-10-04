<?php

namespace App\Models;

use App\Models\TripPrice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Trip extends Model
{
    protected $table = 'trips';

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

    public function prices(): HasMany
    {
        return $this->hasMany(TripPrice::class);
    }

    public function scopeWithPrices(Builder $query): Builder
    {
        return $query->with('prices');
    }
}