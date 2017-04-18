<?php

class MensagemController extends Controller
{
    protected $mensagem;

    public function __construct(ModeloCampeonato $mensagem)
    {
        $this->mensagem = $mensagem;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        return Response::json(Mensagem::get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        $input = Input::all();
        $validation = Validator::make($input, Mensagem::$rules);

        if ($validation->passes())
        {
            $input['id_remetente'] = Auth::getUser()->id;
            if($input['id_destinatario'] != $input['id_remetente']) {
                Mensagem::create($input);
                return Response::json(array('success'=>true));
            } else {
                return Response::json(array('success'=>false,
                    'errors'=>$validation->getMessageBag()->all(),
                    'message'=>'Não é possível enviar mensagem para você mesmo.'),300);
            }

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
        return Response::json(Mensagem::find($id));
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
        $validation = Validator::make($input, Mensagem::$rules);

        if ($validation->passes())
        {
            $mensagem = $this->mensagem->find($id);
            $mensagem->update($input);

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
        Mensagem::destroy($id);

        return Response::json(array('success'=>true));
    }
}
