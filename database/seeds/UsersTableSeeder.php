<?php

class UsersTableSeeder extends Seeder {

	public function run()
	{
		// Uncomment the below to wipe the table clean before populating
		// DB::table('users')->truncate();

        $faker = Faker\Factory::create();

        for($i = 0; $i<32; $i++) {
            $user = User::create(array(
                'nome' => $faker->userName,
                'email' => $faker->email,
                'password' => $faker->word,
                'usuario_tipos_id' => 3
            ));
        }

		// Uncomment the below to run the seeder
		// DB::table('users')->insert($users);
	}

}
