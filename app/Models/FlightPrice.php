<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlightPrice extends Model
{
    protected $table = 'flight_prices';
    protected $fillable = [
        'departure_airport',
        'arrival_airport',
        'departure_date',
        'departure_time',
        'arrival_time',
        'price',
    ];
}