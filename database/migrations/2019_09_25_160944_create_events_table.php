<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('competition_id');
            $table->bigInteger('sid')->index();
            $table->dateTime('start_play');
            $table->string('status')->nullable();
            $table->bigInteger('home_team_id');
            $table->smallInteger('home_score')->nullable();
            $table->bigInteger('away_team_id');
            $table->smallInteger('away_score')->nullable();
            $table->smallInteger('minute')->nullable();
            $table->smallInteger('minute_extra')->nullable();
            $table->string('period')->nullable();
            $table->timestamp('last_update')->nullable();
            $table->timestamps();
            
            $table->unique(['competition_id', 'sid'], 'unique_competitionid_sid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
}
