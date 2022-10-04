<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripPricesTable extends Migration
{
    private const TRIP_PRICES = 'trip_prices';

    public function up()
    {
        Schema::create(self::TRIP_PRICES, function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('trip_id');
            $table->string('resourceCode');
            $table->integer('price');
            $table->string('ticketType')->nullable();
            $table->string('type')->nullable();
            $table->boolean('available')->nullable();
            $table->string('yieldClass')->nullable();
            $table->integer('capacity')->nullable();
            $table->integer('intervalValue')->nullable();
            $table->integer('resourceType')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TRIP_PRICES);
    }
}
