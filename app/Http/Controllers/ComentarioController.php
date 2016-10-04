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

            $post = Post::find($input['post_id']);
            $comentarios = $post->comentarios();

            return Response::json($comentarios);
        }

        return Response::json(array('success'=>false,
            'errors'=>$validation->getMessageBag()->all(),
            'message'=>'There were validation errors.'),300);
    }

    public function curtir() {
        $input = Input::all();
        $comentario = Comentario::find($input['comentario_id']);
        $comentario->curtir($input['users_id']);
        $quantidadeCurtidas = $comentario->quantidadeCurtidas();
        return Response::json(array('success'=>true, 'quantidadeCurtidas'=>$quantidadeCurtidas));
    }
}
