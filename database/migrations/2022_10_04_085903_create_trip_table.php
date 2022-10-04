<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripTable extends Migration
{
    private const TABLE = 'trips';

    public function up()
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->integer('price');
            $table->boolean('bookable');
            $table->boolean('noAccommodationsAvailable');
            $table->boolean('noVehicleCapacity');
            $table->boolean('noPassengerCapacity');
            $table->string('routeCode');
            $table->string('departFrom');
            $table->string('returnFrom');

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
        Schema::dropIfExists(self::TABLE);
    }
}
