<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAbandonCallersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('abandon_callers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('queue');
            $table->string('uniqueid');
            $table->string('position');
            $table->string('origposition');
            $table->string('holdtime');
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
        Schema::dropIfExists('abandon_callers');
    }
}
