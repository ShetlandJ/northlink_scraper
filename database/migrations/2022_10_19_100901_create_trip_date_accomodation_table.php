<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripDateAccomodationTable extends Migration
{
    private const TABLE = 'trip_accomodations';

    public function up()
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trip_id');
            $table->integer('amount');
            $table->boolean('bnBIncluded');
            $table->integer('capacity');
            $table->string('description');
            $table->boolean('extrasIncluded');
            $table->boolean('hasSeaView');
            $table->string('identifier');
            $table->boolean('isAccessibleCabin');
            $table->integer('price');
            $table->string('resourceCode');
            $table->integer('sleeps');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE);
    }
}
