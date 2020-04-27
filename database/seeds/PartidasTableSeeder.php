<?php

use Illuminate\Database\Seeder;

class PartidasTableSeeder extends Seeder {

	public function run()
	{
		// Uncomment the below to wipe the table clean before populating
		// DB::table('partidas')->truncate();

		$idCampeonato = 60;
		$campeonato = Campeonato::find($idCampeonato);

		$partidas = $campeonato->partidas();
		foreach ($partidas as $partida) {
			if($partida->data_placar == null) {
				$dados = array();
				$usuarios = array();
				$dados['id'] = $partida->id;
				foreach($partida->usuarios(false) as $usuario) {
					$novoUsuario = array();
					$novoUsuario['id'] = $usuario['id'];
					$novoUsuario['placar'] = rand(0,6);
					$usuarios[] = $novoUsuario;
				}
				$dados['usuarios'] = $usuarios;
				$campeonato->salvarPlacar($dados);
			}
		}


		// Uncomment the below to run the seeder
		// DB::table('partidas')->insert($partidas);
	}

}
