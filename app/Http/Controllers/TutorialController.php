<?php

class TutorialController extends Controller
{
    /**
     * Tutorial Repository
     *
     * @var tutorial
     */
    protected $tutorial;

    public function __construct(Tutorial $tutorial)
    {
        $this->tutorial = $tutorial;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return Response::json(Tutorial::get());
    }

    /**
     * Display a specific resource.
     *
     * @param  int  $key
     * @return Response
     */
    public function show($id) {
        $tutorial = Tutorial::find($id);
        if(!isset($tutorial)) {
            return null;
        }
        $tutorial->items = $tutorial->items()->get();
        return Response::json($tutorial);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $input = Input::all();
        $validation = Validator::make($input, Tutorial::$rules);

        if ($validation->passes())
        {
            Tutorial::create($input);

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
        return Response::json(Tutorial::find($id));
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
        $validation = Validator::make($input, Tutorial::$rules);

        if ($validation->passes())
        {
            $tutorial = $this->tutorial->find($id);
            $tutorial->update($input);

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
    public function destroy($id)
    {
        Tutorial::destroy($id);

        return Response::json(array('success'=>true));
    }

    public function getVisualizado() {
        $input = Input::all();
        $tela = $input['tela'];
        $idUsuario = Auth::getUser()->id;
        $tutorial = Tutorial::where('tela','=',$tela)->first();
        return DB::table('tutorial_visualizado')->where('tutorial_id','=',$tutorial->id)->where('users_id','=',$idUsuario)->count();
    }

    public function setVisualizado() {
        $input = Input::all();
        $idTutorial = $input['id'];
        $idTutorialAgregado = $input['tutorial_agregado'];
        $idUsuario = Auth::getUser()->id;

        if(DB::table('tutorial_visualizado')->whereRaw('(tutorial_id = '.$idTutorial.' or tutorial_id = '.$idTutorialAgregado.') and users_id = '.$idUsuario)->count() === 0) {
            DB::table('tutorial_visualizado')->insert(array('tutorial_id'=>$idTutorial, 'users_id'=>$idUsuario));
            DB::table('tutorial_visualizado')->insert(array('tutorial_id'=>$idTutorialAgregado, 'users_id'=>$idUsuario));
        }
    }


}
