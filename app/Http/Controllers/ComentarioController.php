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
            $input['texto'] = $this->criarLink($input['texto']);

            Comentario::create($input);

            $atividade = Atividade::find($input['atividade_id']);
            $comentarios = $atividade->comentarios($input['users_id']);

            $evento = NotificacaoEvento::where('valor','=','comentar_post')->first();
            if(isset($evento)) {
                $idEvento = $evento->id;
            }

            $notificacao = new Notificacao();
            $notificacao->id_remetente = $input['users_id'];
            $notificacao->id_destinatario = $atividade->users_id;
            $notificacao->evento_notificacao_id = $idEvento;
            $notificacao->item_id = $atividade->id;
            $notificacao->save();

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
            $input['texto'] = $this->criarLink($input['texto']);
            $dadosComentario = array('id'=>$id, 'texto'=>$input['texto']);
            $comentario->update($dadosComentario);

            return Response::json(array('success'=>true, 'comentario'=>$comentario));
        }

        return Response::json(array('success'=>false,
            'errors'=>$validation->getMessageBag()->all(),
            'message'=>'There were validation errors.'),300);
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
