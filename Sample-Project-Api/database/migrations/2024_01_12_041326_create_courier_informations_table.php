<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourierInformationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courier_informations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('courier')->nullable(true);
            $table->string('tracking_number')->nullable(true);
            $table->dateTime('date_of_pickup')->nullable(true);
            $table->string('notes')->nullable(true);
            $table->string('result')->nullable(true);
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
        Schema::dropIfExists('courier_informations');
    }
}
