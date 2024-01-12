<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddResultToSpecimenForms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('specimen_forms', function (Blueprint $table) {
            $table->string('result')->nullable(true)->after('specimen_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('specimen_forms', function (Blueprint $table) {
            $table->dropColumn('result');
        });
    }
}