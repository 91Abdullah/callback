<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAbandonedCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('abandoned_calls', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number');
            $table->boolean('status');
            $table->dateTime('abandontime');
            $table->string('queue');
            $table->string('position');
            $table->string('originalposition');
            $table->string('holdtime');
            $table->string('uniqueid');
            $table->dateTime('callbacktime')->nullable();
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
        Schema::dropIfExists('abandoned_calls');
    }
}
