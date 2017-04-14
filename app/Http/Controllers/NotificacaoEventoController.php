<?php
/**
 * Created by PhpStorm.
 * User: hugorabelo
 * Date: 4/13/17
 * Time: 10:51 PM
 */
class NotificacaoEventoController extends Controller
{
    /**
     * NotificacaoEvento Repository
     *
     * @var NotificacaoEvento
     */
    protected $notificacaoEvento;

    public function __construct(NotificacaoEvento $notificacaoEvento)
    {
        $this->notificacaoEvento = $notificacaoEvento;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $eventos = NotificacaoEvento::get()->sortBy('descricao')->values();
        $usuarioLogado = Auth::getUser();
        foreach ($eventos as $evento) {
            if(\DB::table('notificacao_email')->where('evento_notificacao_id','=',$evento->id)->where('users_id','=',$usuarioLogado->id)->count('id') > 0) {
                $evento->enabled = true;
            }
        }
        return Response::json($eventos);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        $input = Input::all();
        $validation = Validator::make($input, NotificacaoEvento::$rules);

        if ($validation->passes())
        {
            NotificacaoEvento::create(Input::all());

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
        return Response::json(NotificacaoEvento::find($id));
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
        $validation = Validator::make($input, NotificacaoEvento::$rules);

        if ($validation->passes())
        {
            $notificacaoEvento = $this->notificacaoEvento->find($id);
            $notificacaoEvento->update($input);

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
        NotificacaoEvento::destroy($id);

        return Response::json(array('success'=>true));
    }

}
