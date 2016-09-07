<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCampeonatoTiposTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('campeonatoTipos', function(Blueprint $table) {
			$table->increments('id');
			$table->string('descricao');
			$table->integer('maximo_jogadores_partida');
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
		Schema::drop('campeonatoTipos');
	}

}
