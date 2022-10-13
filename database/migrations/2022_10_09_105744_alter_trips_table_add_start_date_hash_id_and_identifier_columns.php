<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTripsTableAddStartDateHashIdAndIdentifierColumns extends Migration
{
    private const TABLE = 'trips';

    public function up()
    {
        Schema::table(self::TABLE, function (Blueprint $table) {
            $table->date('startDate')->after('routeCode')->nullable();
            $table->string('hashId')->after('startDate')->nullable();
            $table->string('identifier')->after('hashId')->nullable();
        });
    }

    public function down()
    {
        Schema::table(self::TABLE, function (Blueprint $table) {
            $table->dropColumn('startDate');
            $table->dropColumn('hashId');
            $table->dropColumn('identifier');
        });
    }
}
