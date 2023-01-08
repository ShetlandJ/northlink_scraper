<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFlightPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('flight_prices', function (Blueprint $table) {
            $table->id();
            $table->string('departure_airport');
            $table->string('arrival_airport');
            $table->dateTime('departure_date');
            $table->string('departure_time');
            $table->string('arrival_time');
            $table->float('price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('flight_prices');
    }
}
