<?php

use Illuminate\Http\Request;

use App\Http\Requests;

use Carbon\Carbon;

use Illuminate\Support\Collection;

class AgendaController extends Controller
{
    /**
     * Agenda Repository
     *
     * @var Agenda
     */
    protected $comentario;

    public function __construct()
    {
    }

    public function index() {
        //$comentarios = Comentario::get();
        //return Response::json($comentarios);
    }

    public function show($idCampeonato, $idUsuario = null) {
        if($idCampeonato == 'undefined' || $idCampeonato == null) {
            return null;
        }

        $userCampeonato = new CampeonatoUsuario();

        if(!isset($idUsuario)) {
            $idUsuario = Auth::getUser()->id;
        }
        $userCampeonato = $userCampeonato->getID($idUsuario, $idCampeonato);

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

        $hora_inicio = strstr($input['start'], " (", true);
        $hora_inicio = Carbon::parse($hora_inicio);

        $hora_fim = strstr($input['end'], " (", true);
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

        DB::table('agendamento_horario_disponivel')->where('id','=',$id)->update(array('hora_inicio'=>$hora_inicio, 'hora_fim'=>$hora_fim));
    }

    public function destroy($id)
    {
        DB::table('agendamento_horario_disponivel')->where('id','=',$id)->delete();
        return Response::json(array('success' => true));
    }

    public function getMarcados($idEvento) {
        if($idEvento == 'undefined' || $idEvento == null) {
            return null;
        }

        $eventos = DB::table('agendamento_marcacao')->where('horario_agendamento','=',$idEvento)->orderBy('horario_inicio')->get();

        foreach ($eventos as $evento) {
            $usuarioHost = User::find($evento->usuario_host);
            $evento->usuarioHost = $usuarioHost;
        }

        return Response::json($eventos);
    }

    public function listaHorarios($idCampeonato, $idUsuario, $data = null) {
        if($idCampeonato == 'undefined' || $idCampeonato == null) {
            return null;
        }

        $userCampeonato = new CampeonatoUsuario();

        if(!isset($idUsuario)) {
            return null;
        }

        $userCampeonato = $userCampeonato->getID($idUsuario, $idCampeonato);

        $listaHorarios = new Collection();

        $horariosDisponiveis = DB::table('agendamento_horario_disponivel')->where('campeonato_usuarios_id','=',$userCampeonato->id)->orderBy('data')->orderBy('hora_inicio')->get();
        foreach ($horariosDisponiveis as $horario) {
            $item = $listaHorarios->get($horario->data);
            if ($item == null) {
                $item = new Collection();
            }

            $eventosMarcados = DB::table('agendamento_marcacao')->where('horario_agendamento','=',$horario->id)->orderBy('horario_inicio')->get();

            $horaIterator = Carbon::parse($horario->hora_inicio);

            foreach ($eventosMarcados as $evento) {
                $horarioInicio = Carbon::parse($evento->horario_inicio);
                if($evento->usuario_host == $idUsuario) {
                    $adversario = User::find($evento->usuario_convidado);
                } else {
                    $adversario = User::find($evento->usuario_host);
                }
                if($evento->horario_inicio == $horaIterator) {
                    $horaIterator->addMinutes($evento->duracao);
                    $intervalo = array('ocupado', $adversario, $horarioInicio->format('H:i'), $horaIterator->format('H:i'), $horario->id);
                    $item->push($intervalo);
                } else {
                    $intervalo = array('livre', 0, $horaIterator->format('H:i'), $horarioInicio->format('H:i'), $horario->id);
                    $item->push($intervalo);
                    $horaIterator = Carbon::parse($horarioInicio);

                    $horaIterator->addMinutes($evento->duracao);
                    $intervalo = array('ocupado', $adversario, $horarioInicio->format('H:i'), $horaIterator->format('H:i'), $horario->id);
                    $item->push($intervalo);
                }
            }
            if($horaIterator < Carbon::parse($horario->hora_fim)) {
                $horarioFim = Carbon::parse($horario->hora_fim);
                $intervalo = array('livre', 0, $horaIterator->format('H:i'), $horarioFim->format('H:i'), $horario->id);
                $item->push($intervalo);
            }
            $listaHorarios->put($horario->data, $item);
        }

        return $listaHorarios;
    }

    public function agendarPartida() {
        $input = Input::except(array('idCampeonato'));

        $input['status'] = 0;

        /*
         * 1. Verificar se já existe um agendamento para a partida
         * 2. Verificar se o horário é um horário disponibilizado pelo convidado
         * 3. Verificar se o horário está livre dentro do campeonato
         * 4. Verificar se o horário está livre em qualquer campeonato
         */

        DB::table('agendamento_marcacao')->insert($input);

    }

    /*
     * Status Agendamento
     * 0: Status Inicial
     * 1: Confirmado o convite
     * 2: Rejeitado o convite
     * 4: Cancelado (após confirmado)
     * 5: Não realizado
     */

}
