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
            if(isset($input['destinatario_id']) && $input['destinatario_id'] == 'undefined') {
                $input['destinatario_id'] = null;
            }
            $input['texto'] = $this->criarLink($input['texto']);
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
            $input['texto'] = $this->criarLink($input['texto']);
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

    function criarLink ($texto)
    {
        if (!is_string ($texto))
            return $texto;

        $er = "/((http|https|ftp|ftps):\/\/(www\.|.*?\/)?|www\.)([a-zA-Z0-9]+|_|-)+(\.(([0-9a-zA-Z]|-|_|\/|\?|=|&)+))+/i";
        preg_match_all ($er, $texto, $match);

        foreach ($match[0] as $link)
        {
            //coloca o 'http://' caso o link não o possua
            if(stristr($link, "http://") === false && stristr($link, "https://") === false)
            {
                $link_completo = "http://" . $link;
            }else{
                $link_completo = $link;
            }

            $link_len = strlen ($link);

            $web_link = str_replace ("&", "&amp;", $link_completo);
            $texto = str_ireplace ($link, "<a href=\"" . $web_link . "\" target=\"_blank\">". (($link_len > 60) ? substr ($web_link, 0, 25). "...". substr ($web_link, -15) : $web_link) ."</a>", $texto);

        }
        return $texto;
    }

}
