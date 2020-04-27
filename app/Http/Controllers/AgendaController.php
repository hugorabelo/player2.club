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

    public function __construct()
    {
    }

    public function index() {
    }

    public function show($idCampeonato, $idUsuario = null) {
        if($idCampeonato == 'undefined' || $idCampeonato == null) {
            return null;
        }

        $userCampeonato = new CampeonatoUsuario();

        if(!isset($idUsuario)) {
            $idUsuario = Auth::user()->id;
        }

        $userCampeonato = $userCampeonato->getID($idUsuario, $idCampeonato);

        $listaHorarios = new Collection();

        $horariosDisponiveis = AgendamentoHorarioDisponivel::where('campeonato_usuarios_id','=',$userCampeonato->id)->orderBy('data')->orderBy('hora_inicio')->get();
        foreach ($horariosDisponiveis as $horario) {

            $partidasDoCampeonato = Campeonato::find($idCampeonato)->partidas()->pluck('id');

            $eventosMarcados = AgendamentoMarcacao::whereBetween('horario_inicio',array($horario->hora_inicio,$horario->hora_fim))->whereIn('partidas_id',$partidasDoCampeonato)->orderBy('horario_inicio')->get();

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
                        $intervalo = array('situacao'=>$situacao, 'adversario'=>$adversario, 'hora_inicio'=>$horarioInicio->format('Y-m-d H:i:s'), 'hora_fim'=>$horaIterator->format('Y-m-d H:i:s'), 'id'=>$horario->id, 'partida'=>Partida::find($evento->partidas_id), 'usuario_host'=>$evento->usuario_host, 'idHorario'=>$horario->id);
                        $listaHorarios->push($intervalo);
                    } else {
                        $intervalo = array('situacao'=>'livre', 'adversario'=>0, 'hora_inicio'=>$horaIterator->format('Y-m-d H:i:s'), 'hora_fim'=>$horarioInicio->format('Y-m-d H:i:s'), 'id'=>$horario->id, 'partida'=>'', 'usuario_host'=>'', 'idHorario'=>$horario->id);
                        $listaHorarios->push($intervalo);
                        $horaIterator = Carbon::parse($horarioInicio);

                        if($horaIterator < $horario->hora_fim) {
                            $horaIterator->addMinutes($evento->duracao);
                            $intervalo = array('situacao'=>$situacao, 'adversario'=>$adversario, 'hora_inicio'=>$horarioInicio->format('Y-m-d H:i:s'), 'hora_fim'=>$horaIterator->format('Y-m-d H:i:s'), 'id'=>$horario->id, 'partida'=>Partida::find($evento->partidas_id), 'usuario_host'=>$evento->usuario_host, 'idHorario'=>$horario->id);
                            $listaHorarios->push($intervalo);
                        }
                    }
                }
            }
            if($horaIterator < Carbon::parse($horario->hora_fim)) {
                $horarioFim = Carbon::parse($horario->hora_fim);
                $intervalo = array('situacao'=>'livre', 'adversario'=>0, 'hora_inicio'=>$horaIterator->format('Y-m-d H:i:s'), 'hora_fim'=>$horarioFim->format('Y-m-d H:i:s'), 'id'=>$horario->id, 'partida'=>'', 'usuario_host'=>'', 'idHorario'=>$horario->id);
                $listaHorarios->push($intervalo);
            }
        }
        return Response::json($listaHorarios);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        $input = Input::except(array('idCampeonato'));

        $userCampeonato = new CampeonatoUsuario();

        $input['campeonato_usuarios_id'] = $userCampeonato->getID(Auth::user()->id, Input::input('idCampeonato'))->id;

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

        AgendamentoHorarioDisponivel::create($input);

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

        AgendamentoHorarioDisponivel::where('id','=',$id)->update(array('hora_inicio'=>$hora_inicio, 'hora_fim'=>$hora_fim));
    }

    public function destroy($id)
    {
        AgendamentoHorarioDisponivel::where('id','=',$id)->delete();
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


        $horariosDisponiveis = AgendamentoHorarioDisponivel::where('campeonato_usuarios_id','=',$userCampeonato->id)->whereBetween('hora_inicio',array($inicioMes, $fimMes))->orderBy('data')->orderBy('hora_inicio')->get();
        foreach ($horariosDisponiveis as $horario) {
            $item = $listaHorarios->get($horario->data);
            if ($item == null) {
                $item = new Collection();
            }

            $partidasDoCampeonato = Campeonato::find($idCampeonato)->partidas()->pluck('id');
            $eventosMarcados = AgendamentoMarcacao::whereBetween('horario_inicio',array($horario->hora_inicio,$horario->hora_fim))->whereIn('partidas_id',$partidasDoCampeonato)->orderBy('horario_inicio')->get();

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
                        $intervalo = array('situacao'=>$situacao, 'adversario'=>$adversario, 'hora_inicio'=>$horarioInicio->format('H:i'), 'hora_fim'=>$horaIterator->format('H:i'), 'id'=>$horario->id, 'partida'=>Partida::find($evento->partidas_id), 'usuario_host'=>$evento->usuario_host, 'idHorario'=>$horario->id);
                        $item->push($intervalo);
                    } else {
                        $intervalo = array('situacao'=>'livre', 'adversario'=>0, 'hora_inicio'=>$horaIterator->format('H:i'), 'hora_fim'=>$horarioInicio->format('H:i'), 'id'=>$horario->id, 'partida'=>'', 'usuario_host'=>'', 'idHorario'=>$horario->id);
                        $item->push($intervalo);
                        $horaIterator = Carbon::parse($horarioInicio);

                        if($horaIterator < $horario->hora_fim) {
                            $horaIterator->addMinutes($evento->duracao);
                            $intervalo = array('situacao' => $situacao, 'adversario' => $adversario, 'hora_inicio' => $horarioInicio->format('H:i'), 'hora_fim' => $horaIterator->format('H:i'), 'id'=>$horario->id, 'partida'=>Partida::find($evento->partidas_id), 'usuario_host'=>$evento->usuario_host, 'idHorario'=>$horario->id);
                            $item->push($intervalo);
                        }
                    }
                }
            }
            if($horaIterator < Carbon::parse($horario->hora_fim)) {
                $horarioFim = Carbon::parse($horario->hora_fim);
                $intervalo = array('situacao'=>'livre', 'adversario'=>0, 'hora_inicio'=>$horaIterator->format('H:i'), 'hora_fim'=>$horarioFim->format('H:i'), 'id'=>$horario->id, 'partida'=>'', 'usuario_host'=>'', 'idHorario'=>$horario->id);
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
        $existe_agendamento = AgendamentoMarcacao::where('partidas_id','=',$partidas_id)->
                                        where('status','<',2)->count('id');
        if($existe_agendamento) {
            return Response::json(array('success'=>false,
                'error'=>'messages.existe_agendamento_partida',
                'message'=>'There were validation errors.'),300);
        }

        // VALIDA2
        $existe_horario = AgendamentoHorarioDisponivel::
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
        $horario_convidado_indisponivel_campeonato = AgendamentoMarcacao::whereRaw("status < 2 AND ".
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
        $horario_convidado_indisponivel_geral = AgendamentoMarcacao::whereRaw("status < 2 AND ".
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
        $horario_host_indisponivel_campeonato = AgendamentoMarcacao::whereRaw("status < 2 AND ".
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
        $horario_host_indisponivel_geral = AgendamentoMarcacao::whereRaw("status < 2 AND ".
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

        AgendamentoMarcacao::create($registroSalvar);

        $evento = NotificacaoEvento::where('valor','=','agendamento_criado')->first();
        if(isset($evento)) {
            $idEventoNotificacao = $evento->id;
            $this->insereNotificacao($idEventoNotificacao, $usuario_convidado, $registroSalvar['partidas_id']);
        }

        $horario_intervalo = AgendamentoHorarioDisponivel::
        whereRaw("'$horario_inicio' between hora_inicio AND hora_fim and ".
            "campeonato_usuarios_id = (".
            "select id from campeonato_usuarios where users_id = $usuario_convidado".
            " and campeonatos_id = $campeonato_id)")->first();

        if(!($horario_inicio == $horario_intervalo->hora_inicio && $horario_intervalo->hora_fim == (Carbon::parse($horario_inicio)->addMinutes(30)->format('Y-m-d H:i:s')))) {
            if($horario_inicio == $horario_intervalo->hora_inicio) {
                // Se horário do agendamento é no começo do intervalo => Quebrar em duas partes, começando do horário final (horario inicio + duracao)
                $novo_horario_disponivel = array();
                $novo_horario_disponivel['hora_inicio'] = Carbon::parse($horario_inicio)->addMinutes(30)->format('Y-m-d H:i:s');
                $novo_horario_disponivel['hora_fim'] = $horario_intervalo->hora_fim;
                $novo_horario_disponivel['data'] = $horario_intervalo->data;
                $novo_horario_disponivel['campeonato_usuarios_id'] = $horario_intervalo->campeonato_usuarios_id;
                AgendamentoHorarioDisponivel::create($novo_horario_disponivel);

                $horario_intervalo->hora_fim = (Carbon::parse($horario_inicio)->addMinutes(30)->format('Y-m-d H:i:s'));
                AgendamentoHorarioDisponivel::where('id','=',$horario_intervalo->id)->update($horario_intervalo->toArray());
            } else {
                if($horario_intervalo->hora_fim == (Carbon::parse($horario_inicio)->addMinutes(30)->format('Y-m-d H:i:s'))) {
                    // Se horário do agendamento + intervalo é igual ao fim do intervalo => Quebrar em duas partes, com o horário inicial sendo o final do novo intervalo
                    $novo_horario_disponivel = array();
                    $novo_horario_disponivel['hora_inicio'] = $horario_intervalo->hora_inicio;
                    $novo_horario_disponivel['hora_fim'] = $horario_inicio;
                    $novo_horario_disponivel['data'] = $horario_intervalo->data;
                    $novo_horario_disponivel['campeonato_usuarios_id'] = $horario_intervalo->campeonato_usuarios_id;
                    AgendamentoHorarioDisponivel::create($novo_horario_disponivel);

                    $horario_intervalo->hora_inicio = $horario_inicio;
                    AgendamentoHorarioDisponivel::where('id','=',$horario_intervalo->id)->update($horario_intervalo->toArray());
                } else {
                    // Se não, quebrar em 3 partes: 1. do início do intervalo até o início do agendamento / 2. DO início do agendamento ao início do agendamento + duracao / 3. Do início do agendamento + duracao ao final do intervalo
                    $novo_horario_disponivel = array();
                    $novo_horario_disponivel['hora_inicio'] = $horario_intervalo->hora_inicio;
                    $novo_horario_disponivel['hora_fim'] = $horario_inicio;
                    $novo_horario_disponivel['data'] = $horario_intervalo->data;
                    $novo_horario_disponivel['campeonato_usuarios_id'] = $horario_intervalo->campeonato_usuarios_id;
                    AgendamentoHorarioDisponivel::create($novo_horario_disponivel);

                    $novo_horario_disponivel = array();
                    $novo_horario_disponivel['hora_inicio'] = $horario_inicio;
                    $novo_horario_disponivel['hora_fim'] = Carbon::parse($horario_inicio)->addMinutes(30)->format('Y-m-d H:i:s');
                    $novo_horario_disponivel['data'] = $horario_intervalo->data;
                    $novo_horario_disponivel['campeonato_usuarios_id'] = $horario_intervalo->campeonato_usuarios_id;
                    AgendamentoHorarioDisponivel::create($novo_horario_disponivel);

                    $horario_intervalo->hora_inicio = Carbon::parse($horario_inicio)->addMinutes(30)->format('Y-m-d H:i:s');
                    AgendamentoHorarioDisponivel::where('id','=',$horario_intervalo->id)->update($horario_intervalo->toArray());
                }
            }
        }

        $campeonato_usuario_id_host = CampeonatoUsuario::where('users_id','=',$usuario_host)->where('campeonatos_id','=',$campeonato_id)->first(array('id'))->id;
        $existe_horario_host = AgendamentoHorarioDisponivel::whereRaw("'$horario_inicio' between hora_inicio AND hora_fim and ".
            "campeonato_usuarios_id = $campeonato_usuario_id_host")->count();
        if(!$existe_horario_host) {
            $novo_agendamento_horario_disponivel = array();
            $novo_agendamento_horario_disponivel['hora_inicio'] = $horario_inicio;
            $novo_agendamento_horario_disponivel['hora_fim'] = Carbon::parse($horario_inicio)->addMinutes(30)->format('Y-m-d H:i:s');
            $novo_agendamento_horario_disponivel['data'] = Carbon::parse($horario_inicio)->format('Y-m-d');
            $novo_agendamento_horario_disponivel['campeonato_usuarios_id'] = $campeonato_usuario_id_host;

            AgendamentoHorarioDisponivel::create($novo_agendamento_horario_disponivel);
        } else {
            $horario_intervalo_host = AgendamentoHorarioDisponivel::
            whereRaw("'$horario_inicio' between hora_inicio AND hora_fim and ".
                "campeonato_usuarios_id = (".
                "select id from campeonato_usuarios where users_id = $usuario_host".
                " and campeonatos_id = $campeonato_id)")->first();

            if(!($horario_inicio == $horario_intervalo_host->hora_inicio && $horario_intervalo_host->hora_fim == (Carbon::parse($horario_inicio)->addMinutes(30)->format('Y-m-d H:i:s')))) {
                if($horario_inicio == $horario_intervalo_host->hora_inicio) {
                    // Se horário do agendamento é no começo do intervalo => Quebrar em duas partes, começando do horário final (horario inicio + duracao)
                    $novo_horario_disponivel = array();
                    $novo_horario_disponivel['hora_inicio'] = Carbon::parse($horario_inicio)->addMinutes(30)->format('Y-m-d H:i:s');
                    $novo_horario_disponivel['hora_fim'] = $horario_intervalo_host->hora_fim;
                    $novo_horario_disponivel['data'] = $horario_intervalo_host->data;
                    $novo_horario_disponivel['campeonato_usuarios_id'] = $horario_intervalo_host->campeonato_usuarios_id;
                    AgendamentoHorarioDisponivel::create($novo_horario_disponivel);

                    $horario_intervalo_host->hora_fim = (Carbon::parse($horario_inicio)->addMinutes(30)->format('Y-m-d H:i:s'));
                    AgendamentoHorarioDisponivel::where('id','=',$horario_intervalo_host->id)->update($horario_intervalo_host->toArray());
                } else {
                    if($horario_intervalo_host->hora_fim == (Carbon::parse($horario_inicio)->addMinutes(30)->format('Y-m-d H:i:s'))) {
                        // Se horário do agendamento + intervalo é igual ao fim do intervalo => Quebrar em duas partes, com o horário inicial sendo o final do novo intervalo
                        $novo_horario_disponivel = array();
                        $novo_horario_disponivel['hora_inicio'] = $horario_intervalo_host->hora_inicio;
                        $novo_horario_disponivel['hora_fim'] = $horario_inicio;
                        $novo_horario_disponivel['data'] = $horario_intervalo_host->data;
                        $novo_horario_disponivel['campeonato_usuarios_id'] = $horario_intervalo_host->campeonato_usuarios_id;
                        AgendamentoHorarioDisponivel::create($novo_horario_disponivel);

                        $horario_intervalo_host->hora_inicio = $horario_inicio;
                        AgendamentoHorarioDisponivel::where('id','=',$horario_intervalo_host->id)->update($horario_intervalo_host->toArray());
                    } else {
                        // Se não, quebrar em 3 partes: 1. do início do intervalo até o início do agendamento / 2. DO início do agendamento ao início do agendamento + duracao / 3. Do início do agendamento + duracao ao final do intervalo
                        $novo_horario_disponivel = array();
                        $novo_horario_disponivel['hora_inicio'] = $horario_intervalo_host->hora_inicio;
                        $novo_horario_disponivel['hora_fim'] = $horario_inicio;
                        $novo_horario_disponivel['data'] = $horario_intervalo_host->data;
                        $novo_horario_disponivel['campeonato_usuarios_id'] = $horario_intervalo_host->campeonato_usuarios_id;
                        AgendamentoHorarioDisponivel::create($novo_horario_disponivel);

                        $novo_horario_disponivel = array();
                        $novo_horario_disponivel['hora_inicio'] = $horario_inicio;
                        $novo_horario_disponivel['hora_fim'] = Carbon::parse($horario_inicio)->addMinutes(30)->format('Y-m-d H:i:s');
                        $novo_horario_disponivel['data'] = $horario_intervalo_host->data;
                        $novo_horario_disponivel['campeonato_usuarios_id'] = $horario_intervalo_host->campeonato_usuarios_id;
                        AgendamentoHorarioDisponivel::create($novo_horario_disponivel);

                        $horario_intervalo_host->hora_inicio = Carbon::parse($horario_inicio)->addMinutes(30)->format('Y-m-d H:i:s');
                        AgendamentoHorarioDisponivel::where('id','=',$horario_intervalo_host->id)->update($horario_intervalo_host->toArray());
                    }
                }
            }
        }

    }

    public function confirmarAgendamento(Request $request) {
        $idUsuarioConvidado = AgendamentoMarcacao::where('partidas_id','=',$request->partida['id'])->where('status','=',0)->first()->usuario_convidado;
        $idUsuarioHost = AgendamentoMarcacao::where('partidas_id','=',$request->partida['id'])->where('status','=',0)->first()->usuario_host;
        if(Auth::user()->id != $idUsuarioConvidado) {
            return Response::json(array('success'=>false, 'error'=>'usuario_invalido'),300);
        }
        $registroAtualizar = array('status'=>1);
        $qtdeRegistros = AgendamentoMarcacao::where('partidas_id','=',$request->partida['id'])->where('status','=',0)->update($registroAtualizar);

        $evento = NotificacaoEvento::where('valor','=','agendamento_confirmado')->first();
        if(isset($evento)) {
            $idEventoNotificacao = $evento->id;
            $this->insereNotificacao($idEventoNotificacao, $idUsuarioHost, $request->partida['id']);
        }

        if($qtdeRegistros === 0) {
            return Response::json(array('success'=>false),300);
        }
    }

    public function recusarAgendamento(Request $request) {
        $idUsuarioConvidado = AgendamentoMarcacao::where('partidas_id','=',$request->partida['id'])->where('status','=',0)->first()->usuario_convidado;
        $idUsuarioHost = AgendamentoMarcacao::where('partidas_id','=',$request->partida['id'])->where('status','=',0)->first()->usuario_host;
        if(Auth::user()->id != $idUsuarioConvidado) {
            return Response::json(array('success'=>false, 'error'=>'usuario_invalido'),300);
        }
        $registroAtualizar = array('status'=>2);
        $qtdeRegistros = AgendamentoMarcacao::where('partidas_id','=',$request->partida['id'])->where('status','=',0)->update($registroAtualizar);

        $evento = NotificacaoEvento::where('valor','=','agendamento_recusado')->first();
        if(isset($evento)) {
            $idEventoNotificacao = $evento->id;
            $this->insereNotificacao($idEventoNotificacao, $idUsuarioHost, $request->partida['id']);
        }

        if($qtdeRegistros === 0) {
            return Response::json(array('success'=>false),300);
        }
    }

    public function cancelarAgendamento(Request $request) {
        if($request->status == 'pendente') {
            $registroAtualizar = array('status'=>3);
            $statusMarcacao = 0;
            $destinatario = AgendamentoMarcacao::where('partidas_id','=',$request->partida['id'])->where('status','=',$statusMarcacao)->first()->usuario_convidado;
        } else if($request->status == 'ocupado') {
            $registroAtualizar = array('status'=>4);
            $statusMarcacao = 1;
            $idUsuarioConvidado = AgendamentoMarcacao::where('partidas_id','=',$request->partida['id'])->where('status','=',$statusMarcacao)->first()->usuario_convidado;
            $idUsuarioHost = AgendamentoMarcacao::where('partidas_id','=',$request->partida['id'])->where('status','=',$statusMarcacao)->first()->usuario_host;
            $destinatario = Auth::user()->id == $idUsuarioHost ? $idUsuarioConvidado : $idUsuarioHost;
        }
        $qtdeRegistros = AgendamentoMarcacao::where('partidas_id','=',$request->partida['id'])->where('status','=',$statusMarcacao)->update($registroAtualizar);
        if($qtdeRegistros === 0) {
            return Response::json(array('success'=>false),300);
        }

        $evento = NotificacaoEvento::where('valor','=','agendamento_cancelado')->first();
        if(isset($evento)) {
            $idEventoNotificacao = $evento->id;
            $this->insereNotificacao($idEventoNotificacao, $destinatario, $request->partida['id']);
        }

        $motivo = $request->motivo != '' ? $request->motivo : 'undefined';
        $partidaNaoRealidada = array('motivo'=>$motivo, 'users_id'=>$request->users_id, 'partidas_id'=>$request->partida['id']);
        AgendamentoPartidaNaoRealizada::create($partidaNaoRealidada);

        return Response::json(array('success'=>true));
    }

    public function verificaNaoRealizada() {
        /*
         * Se horario_atual > horario_agendamento
         * Se partida não tem resultado
         * Perguntar se a partida foi realizada
         *      se sim -> direciona para a inserção do placar
         *      se não -> pergunta o motivo e insere no partidas não realizadas
         */
    }

    public function getHistoricoAgendamento(Request $partida) {
        $partidasMarcadas = AgendamentoMarcacao::where('partidas_id','=',$partida->id)->orderBy('created_at')->get(array('status', 'usuario_host', 'usuario_convidado', 'horario_inicio', 'created_at'));
        $partidasNaoRealizadas = AgendamentoPartidaNaoRealizada::where('partidas_id','=',$partida->id)->orderBy('created_at')->get(array('motivo', 'users_id', 'created_at'));

        $historico = new Collection();
        foreach ($partidasMarcadas as $marcada) {
            $marcada->evento = 'marcacao';
            $marcada->hora_formatada = Carbon::parse($marcada->created_at)->format('d/m/Y H:i');
            $marcada->usuarioHost = User::find($marcada->usuario_host);
            $marcada->usuarioConvidado = User::find($marcada->usuario_convidado);
            $historico->push($marcada);
        }

        foreach ($partidasNaoRealizadas as $naoRealizada) {
            $naoRealizada->evento = 'nao_realizacao';
            $naoRealizada->hora_formatada = Carbon::parse($naoRealizada->created_at)->format('d/m/Y H:i');
            $naoRealizada->usuario = User::find($naoRealizada->users_id);
            $historico->push($naoRealizada);
        }

        $historicoOrdenado = $historico->sortBy('created_at');
        $historicoOrdenado->values()->all();

        return $historicoOrdenado->values();
    }

    public function justificaPartidaNaoRealizada(Request $partida) {
        $usuarioLogado = Auth::user();
        $partidaNaoRealidada = array('motivo'=>$partida->motivo_nao_realizacao, 'users_id'=>$usuarioLogado->id, 'partidas_id'=>$partida->partidas_id);
        AgendamentoPartidaNaoRealizada::create($partidaNaoRealidada);

        AgendamentoMarcacao::where('id','=',$partida->id)->update(array('status'=>5));

        return Response::json(array('success'=>true));
    }

    private function insereNotificacao($idEventoNotificao, $idDestinatario, $idPartida) {
        $usuarioLogado = Auth::user();
        $partida = Partida::find($idPartida);
        $campeonato = $partida->campeonato();
        if($idDestinatario != $usuarioLogado->id) {
            $notificacao = new Notificacao();
            $notificacao->id_remetente = $usuarioLogado->id;
            $notificacao->id_destinatario = $idDestinatario;
            $notificacao->evento_notificacao_id = $idEventoNotificao;
            $notificacao->item_id = $campeonato->id;
            $notificacao->save();
        }
    }


    /*
     * Status Agendamento
     * 0: Status Inicial
     * 1: Confirmado o convite
     * 2: Rejeitado o convite
     * 3: Cancelado pelo host antes da confirmação
     * 4: Cancelado (após confirmado)
     * 5: Não realizado
     * 6: Realizado
     */

}
