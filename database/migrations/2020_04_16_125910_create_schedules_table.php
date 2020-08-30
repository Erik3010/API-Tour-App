<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->integer('line');
            $table->foreignId('from_place_id');
            $table->foreignId('to_place_id');
            $table->time('departure_time');
            $table->time('arrival_time');
            $table->integer('distance');
            $table->integer('speed');
            $table->timestamps();

            // $table->foreign('from_place_id')->references('id')->on('places');
            // $table->foreign('to_place_id')->references('id')->on('places');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedules');
    }
}
