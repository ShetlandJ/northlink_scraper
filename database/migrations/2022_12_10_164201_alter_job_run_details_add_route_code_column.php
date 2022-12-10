<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterJobRunDetailsAddRouteCodeColumn extends Migration
{
    private string $tableName = 'job_run_details';

    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->string('route_code')
                ->after('job_name')
                ->nullable();
        });
    }

    public function down()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->dropColumn('route_code');
        });
    }
}
