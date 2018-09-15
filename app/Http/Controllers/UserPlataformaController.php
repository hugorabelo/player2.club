<?php
/**
 * Created by PhpStorm.
 * User: hugo
 * Date: 23/02/15
 * Time: 21:26
 */

class UserPlataformaController extends Controller {

    protected $userPlataforma;

    public function __construct(UserPlataforma $userPlataforma)
    {
        $this->userPlataforma = $userPlataforma;
    }

    public function index() {
        return null;
    }

    public function show($id)
    {
        if($id == 'undefined' || $id == null) {
            return null;
        }
        $userPlataformas = UserPlataforma::where('users_id','=',$id)->get();
        foreach($userPlataformas as $plataforma) {
            $plataforma->nome_plataforma = $plataforma->plataforma()->descricao;
            $plataforma->imagem_plataforma = $plataforma->plataforma()->imagem_logomarca;
        }
        return Response::json($userPlataformas);
    }

    public function store()
    {
        $input = Input::all();
        $input['users_id'] = Auth::getUser()->id;
        $validation = Validator::make($input, UserPlataforma::$rules);

        if ($validation->passes())
        {
            $this->userPlataforma->create($input);

            return Response::json(array('success'=>true));
        }

        return Response::json(array('success'=>false,
            'errors'=>$validation->getMessageBag()->all(),
            'message'=>'There were validation errors.'),300);
    }

    public function destroy($id)
    {
        $this->userPlataforma->find($id)->delete();

        return Response::json(array('success'=>true));
    }

}
