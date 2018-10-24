<?php

use Illuminate\Http\Request;

use App\Http\Requests;

use Carbon\Carbon;

class AgendaController extends Controller
{
    /**
     * Agenda Repository
     *
     * @var Agenda
     */
    protected $comentario;

    public function __construct(User $agenda)
    {
        $this->agenda = $agenda;
    }

    public function index() {
        $comentarios = Comentario::get();
        return Response::json($comentarios);
    }

    public function show($idCampeonato) {
        if($idCampeonato == 'undefined' || $idCampeonato == null) {
            return null;
        }
        $userCampeonato = new CampeonatoUsuario();

        $userCampeonato = $userCampeonato->getID(Auth::getUser()->id, $idCampeonato);

        $eventos = DB::table('agendamento_horario_disponivel')->where('campeonato_usuarios_id','=',$userCampeonato->id)->get();

        return Response::json($eventos);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        $input = Input::except(array('idCampeonato'));

        $userCampeonato = new CampeonatoUsuario();

        $input['campeonato_usuarios_id'] = $userCampeonato->getID(Auth::getUser()->id, Input::input('idCampeonato'))->id;

        $data = strstr($input['data'], " (", true);
        $input['data'] = Carbon::parse($data);

        $hora_inicio = strstr($input['hora_inicio'], " (", true);
        $hora_inicio = Carbon::parse($hora_inicio);

        $hora_fim = strstr($input['hora_fim'], " (", true);
        $hora_fim = Carbon::parse($hora_fim);

        if($hora_fim <= $hora_inicio) {
            return Response::json(array('success'=>false,
                'error'=>'messages.hora_final_maior_inicial',
                'message'=>'There were validation errors.'),300);
        }

        if($hora_inicio->diffInMinutes($hora_fim, false) < 30) {
            return Response::json(array('success'=>false,
                'error'=>'messages.intervalo_pequeno',
                'message'=>'There were validation errors.'),300);
        }

        $input['hora_inicio'] = $hora_inicio;
        $input['hora_fim'] = $hora_fim;

        DB::table('agendamento_horario_disponivel')->insert($input);

        return Response::json(array('success'=>true));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $input = array_except(Input::all(), array('_method', '_token'));
        $validation = Validator::make($input, Comentario::$rules);

        if ($validation->passes())
        {
            $comentario = $this->comentario->find($id);
            $input['texto'] = $this->criarLink($input['texto']);
            $dadosComentario = array('id'=>$id, 'texto'=>$input['texto']);
            $comentario->update($dadosComentario);

            return Response::json(array('success'=>true, 'comentario'=>$comentario));
        }

        return Response::json(array('success'=>false,
            'errors'=>$validation->getMessageBag()->all(),
            'message'=>'There were validation errors.'),300);
    }

}
