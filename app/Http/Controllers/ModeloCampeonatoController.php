<?php

/**
 * Created by PhpStorm.
 * User: hugo
 * Date: 26/06/16
 * Time: 18:32
 */
class ModeloCampeonatoController extends Controller
{
    /**
     * ModeloCampeonato Repository
     *
     * @var ModeloCampeonato
     */
    protected $modeloCampeonato;

    public function __construct(ModeloCampeonato $modeloCampeonato)
    {
        $this->modeloCampeonato = $modeloCampeonato;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        return Response::json(ModeloCampeonato::get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        $input = Input::all();
        $validation = Validator::make($input, ModeloCampeonato::$rules);

        if ($validation->passes())
        {
            ModeloCampeonato::create(Input::all());

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
        return Response::json(ModeloCampeonato::find($id));
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
        $validation = Validator::make($input, ModeloCampeonato::$rules);

        if ($validation->passes())
        {
            $modeloCampeonato = $this->modeloCampeonato->find($id);
            $modeloCampeonato->update($input);

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
        ModeloCampeonato::destroy($id);

        return Response::json(array('success'=>true));
    }

    public function getCriteriosClassificacao($id) {
        $modeloCampeonato = $this->modeloCampeonato->find($id);
        return Response::json($modeloCampeonato->criteriosClassificacao());
    }
}
