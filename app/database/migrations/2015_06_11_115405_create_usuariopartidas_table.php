<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsuarioPartidasTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('usuarioPartidas', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('posicao');
			$table->integer('placar');
			$table->integer('pontuacao');
			$table->date('data_placar');
			$table->integer('partidas_id');
			$table->integer('users_id');
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
		Schema::drop('usuarioPartidas');
	}

}
