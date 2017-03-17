<?php

use Illuminate\Http\Request;

use App\Http\Requests;

class ComentarioController extends Controller
{
    /**
     * Comentario Repository
     *
     * @var Comentario
     */
    protected $comentario;

    public function __construct(Comentario $comentario)
    {
        $this->comentario = $comentario;
    }

    public function index() {
        $comentarios = Comentario::get();
        return Response::json($comentarios);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        $input = Input::all();
        $validation = Validator::make($input, Comentario::$rules);

        if ($validation->passes())
        {
            Comentario::create($input);

            $atividade = Atividade::find($input['atividade_id']);
            $comentarios = $atividade->comentarios($input['users_id']);

            return Response::json($comentarios);
        }

        return Response::json(array('success'=>false,
            'errors'=>$validation->getMessageBag()->all(),
            'message'=>'There were validation errors.'),300);
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
            $dadosComentario = array('id'=>$id, 'texto'=>$input['texto']);
            $comentario->update($dadosComentario);

            return Response::json(array('success'=>true, 'comentario'=>$comentario));
        }

        return Response::json(array('success'=>false,
            'errors'=>$validation->getMessageBag()->all(),
            'message'=>'There were validation errors.'),300);
    }

}
