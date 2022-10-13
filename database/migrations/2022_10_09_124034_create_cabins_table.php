<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCabinsTable extends Migration
{
    private const TABLE = 'cabins';

    public function up()
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->id();

            $table->string('resourceCode');
            $table->string('description');
            $table->boolean('extrasIncluded');
            $table->boolean('bnBIncluded');
            $table->boolean('isAccessibleCabin');
            $table->boolean('hasSeaView');
            $table->integer('sleeps');
            $table->integer('amount');
            $table->string('identifier');
            $table->integer('capacity');
            $table->integer('price');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE);
    }
}
