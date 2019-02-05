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

        if(!isset($data)) {
            $inicioMes = Carbon::now()->firstOfMonth();
            $fimMes = Carbon::now()->endOfMonth();
        } else {
            $data = strstr($data, " (") ? strstr($data, " (", true) : $data;
            $inicioMes = Carbon::parse($data)->firstOfMonth();
            $fimMes = Carbon::parse($data)->endOfMonth();
        }

        $horariosDisponiveis = DB::table('agendamento_horario_disponivel')->where('campeonato_usuarios_id','=',$userCampeonato->id)->whereBetween('hora_inicio',array($inicioMes, $fimMes))->orderBy('data')->orderBy('hora_inicio')->get();
        foreach ($horariosDisponiveis as $horario) {
            $item = $listaHorarios->get($horario->data);
            if ($item == null) {
                $item = new Collection();
            }

            //TODO: FIltrar partidas do campeonato
            $partidasDoCampeonato = Campeonato::find($idCampeonato)->partidas()->pluck('id');
            $eventosMarcados = DB::table('agendamento_marcacao')->whereBetween('horario_inicio',array($horario->hora_inicio,$horario->hora_fim))->whereIn('partidas_id',$partidasDoCampeonato)->orderBy('horario_inicio')->get();

            $horaIterator = Carbon::parse($horario->hora_inicio);

            foreach ($eventosMarcados as $evento) {
                // verificar se o evento é válido
                if($evento->status < 2) {
                    $situacao = $evento->status == 1 ? 'ocupado' : 'pendente';
                    $horarioInicio = Carbon::parse($evento->horario_inicio);
                    if($evento->usuario_host == $idUsuario) {
                        $adversario = User::find($evento->usuario_convidado);
                    } else {
                        $adversario = User::find($evento->usuario_host);
                    }
                    if($evento->horario_inicio == $horaIterator) {
                        $horaIterator->addMinutes($evento->duracao);
                        $intervalo = array('situacao'=>$situacao, 'adversario'=>$adversario, 'hora_inicio'=>$horarioInicio->format('H:i'), 'hora_fim'=>$horaIterator->format('H:i'));
                        $item->push($intervalo);
                    } else {
                        $intervalo = array('situacao'=>'livre', 'adversario'=>0, 'hora_inicio'=>$horaIterator->format('H:i'), 'hora_fim'=>$horarioInicio->format('H:i'));
                        $item->push($intervalo);
                        $horaIterator = Carbon::parse($horarioInicio);

                        $horaIterator->addMinutes($evento->duracao);
                        $intervalo = array('situacao'=>$situacao, 'adversario'=>$adversario, 'hora_inicio'=>$horarioInicio->format('H:i'), 'hora_fim'=>$horaIterator->format('H:i'));
                        $item->push($intervalo);
                    }
                }
            }
            if($horaIterator < Carbon::parse($horario->hora_fim)) {
                $horarioFim = Carbon::parse($horario->hora_fim);
                $intervalo = array('situacao'=>'livre', 'adversario'=>0, 'hora_inicio'=>$horaIterator->format('H:i'), 'hora_fim'=>$horarioFim->format('H:i'));
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
         * VALIDA1. Verificar se já existe um agendamento para a partida
         * VALIDA2. Verificar se o horário é um horário disponibilizado pelo convidado
         * VALIDA3. Verificar se o horário do convidado está livre dentro do campeonato
         * VALIDA4. Verificar se o horário do convidado está livre em qualquer campeonato
         * VALIDA5. Verificar se o horário do host está livre dentro do campeonato
         * VALIDA6. Verificar se o horário do host está livre em qualquer campeonato
         */

        extract($input);
        /**
         * @var $partidas_id integer,
         * @var $usuario_host integer,
         * @var $usuario_convidado integer,
         * @var $horario_inicio timestamp,
         * @var $duracao integer,
         * @var $campeonato_id integer,
         * @var $status integer,
         */


        // VALIDA1
        $existe_agendamento = DB::table('agendamento_marcacao')->where('partidas_id','=',$partidas_id)->
                                        where('status','<',2)->count('id');
        if($existe_agendamento) {
            return Response::json(array('success'=>false,
                'error'=>'messages.existe_agendamento_partida',
                'message'=>'There were validation errors.'),300);
        }

        // VALIDA2
        $existe_horario = DB::table('agendamento_horario_disponivel')->
                            whereRaw("'$horario_inicio' between hora_inicio AND hora_fim and ".
                            "campeonato_usuarios_id = (".
	                            "select id from campeonato_usuarios where users_id = $usuario_convidado".
                                " and campeonatos_id = $campeonato_id)")->count();
        if(!$existe_horario) {
            return Response::json(array('success'=>false,
                'error'=>'messages.nao_existe_horario_disponivel',
                'message'=>'There were validation errors.'),300);
        }

        // VALIDA3
        $horario_convidado_indisponivel_campeonato = DB::table('agendamento_marcacao')->whereRaw("status < 2 AND ".
                                            "horario_inicio = '$horario_inicio' AND ".
                                            "(usuario_host = $usuario_convidado OR usuario_convidado = $usuario_convidado) AND ".
                                            "partidas_id IN (".
                                                "select id from partidas where fase_grupos_id IN (".
                                                    "select id from fase_grupos where campeonato_fases_id  IN (".
                                                        "select id from campeonato_fases where campeonatos_id = $campeonato_id".
                                                    ")".
                                                ")".
                                            ")")->count();
        if($horario_convidado_indisponivel_campeonato) {
            return Response::json(array('success'=>false,
                'error'=>'messages.horario_convidado_indisponivel_campeonato',
                'message'=>'There were validation errors.'),300);
        }

        // VALIDA 4
        $horario_convidado_indisponivel_geral = DB::table('agendamento_marcacao')->whereRaw("status < 2 AND ".
                                        "horario_inicio = '$horario_inicio' AND ".
                                        "(usuario_host = $usuario_convidado OR usuario_convidado = $usuario_convidado) AND ".
                                            "partidas_id IN (".
                                                "select id from partidas where fase_grupos_id IN (".
                                                "select id from fase_grupos where campeonato_fases_id  IN (".
                                                    "select id from campeonato_fases where campeonatos_id <> $campeonato_id".
                                                ")".
                                            ")".
                                        ")")->count();
        if($horario_convidado_indisponivel_geral) {
            return Response::json(array('success'=>false,
                'error'=>'messages.horario_convidado_indisponivel_geral',
                'message'=>'There were validation errors.'),300);
        }

        // VALIDA5
        $horario_host_indisponivel_campeonato = DB::table('agendamento_marcacao')->whereRaw("status < 2 AND ".
                                                "horario_inicio = '$horario_inicio' AND ".
                                                "(usuario_host = $usuario_host OR usuario_convidado = $usuario_host) AND ".
                                                    "partidas_id IN (".
                                                        "select id from partidas where fase_grupos_id IN (".
                                                            "select id from fase_grupos where campeonato_fases_id  IN (".
                                                                "select id from campeonato_fases where campeonatos_id = $campeonato_id".
                                                            ")".
                                                        ")".
                                                    ")")->count();
        if($horario_host_indisponivel_campeonato) {
            return Response::json(array('success'=>false,
                'error'=>'messages.horario_host_indisponivel_campeonato',
                'message'=>'There were validation errors.'),300);
        }

        // VALIDA 6
        $horario_host_indisponivel_geral = DB::table('agendamento_marcacao')->whereRaw("status < 2 AND ".
                                            "horario_inicio = '$horario_inicio' AND ".
                                            "(usuario_host = $usuario_host OR usuario_convidado = $usuario_host) AND ".
                                            "partidas_id IN (".
                                                "select id from partidas where fase_grupos_id IN (".
                                                    "select id from fase_grupos where campeonato_fases_id  IN (".
                                                        "select id from campeonato_fases where campeonatos_id <> $campeonato_id".
                                                    ")".
                                                ")".
                                            ")")->count();
        if($horario_host_indisponivel_geral) {
            return Response::json(array('success'=>false,
                'error'=>'messages.horario_host_indisponivel_geral',
                'message'=>'There were validation errors.'),300);
        }

        $registroSalvar = array_except($input, 'campeonato_id');

        DB::table('agendamento_marcacao')->insert($registroSalvar);

        $campeonato_usuario_id_host = CampeonatoUsuario::where('users_id','=',$usuario_host)->where('campeonatos_id','=',$campeonato_id)->first(array('id'))->id;
        $existe_horario_host = DB::table('agendamento_horario_disponivel')->whereRaw("'$horario_inicio' between hora_inicio AND hora_fim and ".
            "campeonato_usuarios_id = $campeonato_usuario_id_host")->count();
        if(!$existe_horario_host) {
            $novo_agendamento_horario_disponivel = array();
            $novo_agendamento_horario_disponivel['hora_inicio'] = $horario_inicio;
            $novo_agendamento_horario_disponivel['hora_fim'] = Carbon::parse($horario_inicio)->addMinutes(30)->format('Y-m-d H:i:s');
            $novo_agendamento_horario_disponivel['data'] = Carbon::parse($horario_inicio)->format('Y-m-d');
            $novo_agendamento_horario_disponivel['campeonato_usuarios_id'] = $campeonato_usuario_id_host;

            DB::table('agendamento_horario_disponivel')->insert($novo_agendamento_horario_disponivel);
        }

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
