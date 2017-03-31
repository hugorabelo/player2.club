<?php

class PermissaoController extends Controller {

	/**
	 * Permissao Repository
	 *
	 * @var Permissao
	 */
	protected $permissao;

	public function __construct(Permissao $permissao)
	{
		$this->permissao = $permissao;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index() {
		return Response::json(Permissao::get());
	}

	public function show($id_perfil) {
		$permissoes = Permissao::where('usuario_tipos_id', '=', $id_perfil)->get(array('menu_id'));
		return Response::json($permissoes);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store() {
		$input = Input::all();

		$usuario_tipos_id = $input['usuario_tipos_id'];

		$permissoes_apagar = Permissao::where('usuario_tipos_id', '=', $usuario_tipos_id)->get();
		foreach($permissoes_apagar as $apaga) {
			Permissao::destroy($apaga->id);
		}

		$lista_permissoes = $input['lista'];
		foreach($lista_permissoes as $permissao=>$valida) {
			if($valida == 'true') {
				Permissao::create(array('usuario_tipos_id'=>$usuario_tipos_id, 'menu_id'=>$permissao));
			}
		}

		return Response::json(array('success'=>true));

	}

	public function reportarBug() {
		$input = Input::except('files');
		$inputFiles = Input::all();
		$files = isset($inputFiles['files'])? $inputFiles['files'] : array();
		$texto = isset($input['texto']) ? $input['texto']: '';

		Mail::send('emailBugReport', ['conteudo' => $texto], function($message) use ($input, $files) {
			$message->from('contato@player2.club', $name = 'player2.club');
			$message->to('incoming+hugorabelo/ligavirtual+FQ9KyRqzmyXwtCz_eD8u@gitlab.com', $name = 'GitLab');
			$message->subject($input['titulo']);

            if($files != null) {
                foreach ($files as $arquivo) {
                    if (isset($arquivo) && $arquivo->isValid()) {
                        $destinationPath = 'uploads/bugs/';
                        $fileName = 'imagepost_' . str_replace('.', '', microtime(true)) . '.' . $arquivo->getClientOriginalExtension();
                        $arquivo->move($destinationPath, $fileName);
                        $message->attach($destinationPath . $fileName);
                    }
                }
            }
		});

        return Response::json(array('success'=>true));
	}

}
