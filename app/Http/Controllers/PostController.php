<?php

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

    public function show($id) {
        $post = Post::find($id);
        return Response::json($post);
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

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $input = array_except(Input::all(), array('_method', '_token'));
        $validation = Validator::make($input, Post::$rules);

        if ($validation->passes())
        {
            $post = $this->post->find($id);
            $dadosPost = array('id'=>$id, 'texto'=>$input['texto']);
            $post->update($dadosPost);

            return Response::json(array('success'=>true, 'post'=>$post));
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
        $post = $this->post->find($id);
        $idUsuario = $post->users_id;
        $post->delete();

        return Response::json(array('success'=>true, 'idUsuario'=>$idUsuario));
    }

    public function getComentarios() {
        $input = Input::all();
        $post = Post::find($input['idPost']);
        $comentarios = $post->comentarios($input['idUsuarioLeitor']);
        return Response::json($comentarios);
    }

    public function curtir() {
        $input = Input::all();
        $post = Post::find($input['post_id']);
        $post->curtir($input['users_id']);
        $quantidadeCurtidas = $post->quantidadeCurtidas();
        return Response::json(array('success'=>true, 'quantidadeCurtidas'=>$quantidadeCurtidas));
    }

    public function usuarioCurtiu() {
        $input = Input::all();
        $post = Post::find($input['post_id']);
        $curtiu = $post->curtiu($input['users_id']);
        return Response::json(array('success'=>true, 'curtiu'=>$curtiu));
    }
}
