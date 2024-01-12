<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpecimenFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('specimen_forms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tracking_number')->nullable(true);
            $table->boolean('checked')->default(true); 
            $table->string("type_of_sample")->nullable(true);
            $table->string("baby_last_name")->nullable(true);
            $table->string("baby_first_name")->nullable(true);
            $table->string("for_multiple_births")->nullable(true);
            $table->string("mothers_first_name")->nullable(true);
            $table->dateTime("date_and_time_of_birth")->nullable(true);
            $table->string("sex")->nullable(true);
            $table->float("babys_weight_in_grams")->nullable(true);
            $table->dateTime("date_and_time_of_collection")->nullable(true);
            $table->integer("age_of_gestation_in_weeks")->nullable(true);
            $table->string("specimens")->nullable(true);
            $table->string("place_of_collection")->nullable(true);
            $table->string("place_of_birth")->nullable(true);
            $table->string("attending_practitioner")->nullable(true);
            $table->string("specimen_status")->nullable(true);
            $table->string("practitioner_profession_other")->nullable(true);
            $table->string("practitioner_profession")->nullable(true);
            $table->string("practitioners_day_contact_number")->nullable(true);
            $table->string("practitioners_mobile_number")->nullable(true);
            $table->string("baby_status")->nullable(true);
            $table->string("baby_status_cont")->nullable(true);
            $table->string("name_of_parent")->nullable(true);
            $table->string("number_and_street")->nullable(true);
            $table->string("barangay_or_city")->nullable(true);
            $table->string("province")->nullable(true);
            $table->string("zip_code")->nullable(true);
            $table->string("contact_number_of_parent")->nullable(true);
            $table->string("additional_contact_number")->nullable(true);        
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
        Schema::dropIfExists('specimen_forms');
    }
}
