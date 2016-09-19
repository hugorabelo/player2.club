<?php

class ExampleTest extends TestCase {

    protected $baseUrl = '';

	/**
	 * A basic functional test example.
	 *
	 * @return void
	 */
	public function testBasicExample()
	{
		$response = $this->call('GET', '/');

		$this->assertEquals(200, $response->getStatusCode());
	}

	public function testeApiCampeonato() {
        $this->get('/api/campeonato/37')
            ->seeJsonStructure([
                "id",
                "descricao",
                "regras",
                "jogos_id",
                "campeonato_tipos_id",
                "created_at",
                "updated_at",
                "plataformas_id",
                "acesso_campeonato_id",
                "criador"
            ]);
    }

}
