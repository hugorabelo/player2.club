<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCampeonatosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('campeonatos', function(Blueprint $table) {
			$table->increments('id');
			$table->string('descricao');
			$table->text('detalhes');
			$table->integer('jogos_id');
			$table->integer('campeonatotipos_id');
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
		Schema::drop('campeonatos');
	}

}
