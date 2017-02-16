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
        $input = Input::except('imagens');
        $inputImagens = Input::all();
        $imagens = isset($inputImagens['imagens'])? $inputImagens['imagens'] : array();
        if($imagens == 'undefined') {
            $imagens = array();
        }
        $validation = Validator::make($input, Post::$rules);

        if ($validation->passes())
        {

            $post = Post::create($input);

            //TODO Inserir Imagens capturadas do array imagens
            foreach($imagens as $arquivo) {
                if (isset($arquivo) && $arquivo->isValid()) {
                    $destinationPath = 'uploads/imagens/';
                    $fileName = 'imagepost_'.str_replace('.', '', microtime(true)).'.'.$arquivo->getClientOriginalExtension();
                    $arquivo->move($destinationPath, $fileName);

                    ImagemPost::create(array('url'=>$fileName, 'post_id'=>$post->id));
                }
            }

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
        $input = array_except(Input::all(), array('_method', '_token', 'imagens'));
        $inputImagens = Input::all();
        $imagens = isset($inputImagens['imagens'])? $inputImagens['imagens'] : array();
        if($imagens == 'undefined') {
            $imagens = array();
        }

        $validation = Validator::make($input, Post::$rules);

        if ($validation->passes())
        {
            $post = $this->post->find($id);
            $dadosPost = array('id'=>$id, 'texto'=>$input['texto']);
            $post->update($dadosPost);

            if(isset($input['imagensRemover']) && is_array($input['imagensRemover'])) {
                foreach ($input['imagensRemover'] as $imagemRemovida) {
                    ImagemPost::destroy($imagemRemovida);
                }
            }

            foreach($imagens as $arquivo) {
                if (isset($arquivo) && $arquivo->isValid()) {
                    $destinationPath = 'uploads/imagens/';
                    $fileName = 'imagepost_'.str_replace('.', '', microtime(true)).'.'.$arquivo->getClientOriginalExtension();
                    $arquivo->move($destinationPath, $fileName);

                    ImagemPost::create(array('url'=>$fileName, 'post_id'=>$post->id));
                }
            }

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

    public function getImagens($id) {
        $listaImagens = ImagemPost::where('post_id', '=', $id)->get(array('id', 'url'));
        return Response::json($listaImagens);
    }

}
