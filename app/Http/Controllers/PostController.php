<?php

use Illuminate\Http\Request;

use App\Http\Requests;

class PostController extends Controller
{
    /**
     * Post Repository
     *
     * @var Post
     */
    protected $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        $input = Input::all();
        $validation = Validator::make($input, Post::$rules);

        if ($validation->passes())
        {
            Post::create(Input::all());

            return Response::json(array('success'=>true));
        }

        return Response::json(array('success'=>false,
            'errors'=>$validation->getMessageBag()->all(),
            'message'=>'There were validation errors.'),300);
    }

    public function curtir() {
        //TODO verificar se usuário já curtiu post
        $input = Input::all();
        $post = Post::find($input['post_id']);
        $post->curtir($input['users_id']);
        $quantidadeCurtidas = $post->quantidadeCurtidas();
        return Response::json($quantidadeCurtidas);
    }
}
