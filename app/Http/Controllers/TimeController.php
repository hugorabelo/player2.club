<?php
/**
 * Created by PhpStorm.
 * User: hugorabelo
 * Date: 4/23/17
 * Time: 9:01 PM
 */
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Collection;

class TimeController extends Controller
{
    /**
     * Time Repository
     *
     * @var Time
     */
    protected $time;

    public function __construct(NotificacaoEvento $time)
    {
        $this->time = $time;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $times = Time::get()->sortBy('nome')->values();
        return Response::json($times);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        $input = Input::all();
        $validation = Validator::make($input, Time::$rules);

        if ($validation->passes())
        {
            Time::create(Input::all());

            return Response::json(array('success'=>true));
        }

        return Response::json(array('success'=>false,
            'errors'=>$validation->getMessageBag()->all(),
            'message'=>'There were validation errors.'),300);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        return Response::json(Time::find($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $input = array_except(Input::all(), '_method');
        $validation = Validator::make($input, Time::$rules);

        if ($validation->passes())
        {
            $time = $this->time->find($id);
            $time->update($input);

            return Response::json(array('success'=>true));
        }

        return Response::json(array('success'=>false,
            'errors'=>$validation->getMessageBag()->all(),
            'message'=>'There were validation errors.'),300);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        Time::destroy($id);

        return Response::json(array('success'=>true));
    }

    public function getTimesPorModelo($idModeloCampeonato) {
        $times = Time::where('modelo_campeonato_id', '=', $idModeloCampeonato)->orderBy('nome')->get();
        return Response::json($times);
    }

    public function getBaseFifa() {
        // Endereco: https://www.easports.com/br/fifa/ultimate-team/api/fut/item?jsonParamObject={"page":1,"quality":"bronze,silver,gold,rare_bronze,rare_silver,rare_gold"}
        $url = 'http://www.easports.com/br/fifa/ultimate-team/api/fut/item';
        $cliente = new Client(['base_uri' => 'http://www.easports.com/br/fifa/ultimate-team/api/fut/']);
        $response = $cliente->request('GET', 'item', [
            'query' => [
                'jsonParamObject' => '{"page":1,"quality":"bronze,silver,gold,rare_bronze,rare_silver,rare_gold"}'
            ]
        ]);
        $objetos = json_decode($response->getBody(), true);
        $numeroPaginas = $objetos['totalPages'];

        for ($i = 1; $i<= $numeroPaginas; $i++) {
            $response = $cliente->request('GET', 'item', [
                'query' => [
                    'jsonParamObject' => '{"page":'.$i.',"quality":"bronze,silver,gold,rare_bronze,rare_silver,rare_gold"}'
                ]
            ]);
            $objetos = json_decode($response->getBody(), true);
            $items = $objetos['items'];


            // Salvar Nações

            $times = new Collection();
            $ligas = new Collection();
            $nacoes = new Collection();
            foreach ($items as $item) {
                $liga = $item['league'];
                $ligas->put($liga['id'], $liga);

                $nacao = $item['nation'];
                $nacoes->put($nacao['id'], $nacao);

                $time = $item['club'];
                $time['id_liga'] = $liga['id'];
                $times->put($time['id'], $time);
            }

            // Salvar Ligas
            foreach ($ligas as $liga) {
                $novaLiga = new LigaBase();
                $novaLiga->id = $liga['id'];
                $novaLiga->nome = $liga['name'];
                $novaLiga->abreviacao = $liga['abbrName'];

                if(!LigaBase::find($novaLiga->id)) {
                    $novaLiga->save();
                }
            }


            // Salvar Times
            foreach ($times as $time) {
                $novoTime = new Time();
                $novoTime->id = $time['id'];
                $novoTime->descricao = $time['abbrName'];
                $novoTime->nome = $time['name'];

                $fileDistintivo = $time['imageUrls']['normal']['large'];
                $nomeDistintivo = 'distintivos/distintivo' . $novoTime->id.'.png';
                $novoTime->distintivo = $nomeDistintivo;

                $arquivo = file_get_contents($fileDistintivo, "r" );
                file_put_contents( "uploads/usuarios/$nomeDistintivo", $arquivo, FILE_APPEND );

                $novoTime->modelo_campeonato_id = 1;
                $novoTime->liga_base_id = $time['id_liga'];

                if(!Time::find($novoTime->id)) {
                    $novoTime->save();
                }
            }
        }

        return $times;
    }

}
