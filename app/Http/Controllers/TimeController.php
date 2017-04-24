<?php
/**
 * Created by PhpStorm.
 * User: hugorabelo
 * Date: 4/23/17
 * Time: 9:01 PM
 */
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
        $times = Time::get()->sortBy('descricao')->values();
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
        $times = Time::where('modelo_campeonato_id', '=', $idModeloCampeonato)->orderBy('descricao')->get();
        return Response::json($times);
    }

}
