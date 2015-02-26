<?php

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Eloquent::unguard();

		// $this->call('UserTableSeeder');
		$this->call('UsuariotiposTableSeeder');
		$this->call('PlataformasTableSeeder');
		$this->call('CampeonatotiposTableSeeder');
		$this->call('JogosTableSeeder');
		$this->call('UsersTableSeeder');
		$this->call('CampeonatosTableSeeder');
		$this->call('Campeonato_adminsTableSeeder');
		$this->call('CampeonatoadminsTableSeeder');
	}

}
