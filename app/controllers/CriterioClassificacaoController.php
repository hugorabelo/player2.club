<?php

/**
 * Created by PhpStorm.
 * User: hugo
 * Date: 26/06/16
 * Time: 18:25
 */
class CriterioClassificacaoController extends BaseController
{
    /**
     * CriterioClassificacao Repository
     *
     * @var CriterioClassificacao
     */
    protected $criterioClassificacao;

    public function __construct(CriterioClassificacao $criterioClassificacao)
    {
        $this->criterioClassificacao = $criterioClassificacao;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        return Response::json(CriterioClassificacao::get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        $input = Input::all();
        $validation = Validator::make($input, CriterioClassificacao::$rules);

        if ($validation->passes())
        {
            CriterioClassificacao::create(Input::all());

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
        return Response::json(CriterioClassificacao::find($id));
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
        $validation = Validator::make($input, CriterioClassificacao::$rules);

        if ($validation->passes())
        {
            $criterioClassificacao = $this->criterioClassificacao->find($id);
            $criterioClassificacao->update($input);

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
        CriterioClassificacao::destroy($id);

        return Response::json(array('success'=>true));
    }
}
