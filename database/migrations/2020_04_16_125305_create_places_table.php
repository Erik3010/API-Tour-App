<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlacesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('places', function (Blueprint $table) {
            $table->id('id');
            $table->string('name', 100);
            $table->enum('type', ['ATTRACTIONS', 'RESTAURANTS']);
            $table->float('latitude');
            $table->float('longitude');
            $table->integer('x');
            $table->integer('y');
            $table->string('image_path', 50);
            $table->text('description');
            $table->time('open_time');
            $table->time('close_time');

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
        Schema::dropIfExists('places');
    }
}
