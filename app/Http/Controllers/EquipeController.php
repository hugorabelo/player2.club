<?php
/**
 * Created by PhpStorm.
 * User: hugorabelo
 * Date: 5/26/17
 * Time: 11:47 PM
 */

class EquipeController extends Controller
{

    /**
     * Equipe Repository
     *
     * @var Equipe
     */
    protected $equipe;

    public function __construct(Equipe $equipe)
    {
        $this->equipe = $equipe;
    }

    public function show($id)
    {
        $equipe = Equipe::find($id);
        return Response::json($equipe);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $equipes = Equipe::get();
        return Response::json($equipes->values()->all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {

        $input = Input::all();
        $validation = Validator::make($input, Equipe::$rules);

        if ($validation->passes()) {
            /*
             * Movendo o arquivo para o diretório correto
             */

            $arquivo = Input::hasFile('logotipo') ? Input::file('logotipo')
                : null;

            if (isset($arquivo) && $arquivo->isValid()) {
                $destinationPath = 'uploads/';
                $fileName = 'equipe_' . str_replace('.', '', microtime(true)) . '.' . $arquivo->getClientOriginalExtension();
                $arquivo->move($destinationPath, $fileName);
                $input['logotipo'] = $fileName;
            }

            $equipe = Equipe::create($input);

            return Response::json(array('success' => true));
        }

        return Response::json(array('success' => false,
            'errors' => $validation->getMessageBag()->all(),
            'message' => 'There were validation errors.'), 300);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        return Response::json(Equipe::find($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id)
    {
        $input = array_except(Input::all(), array('_method'));
        $validation = Validator::make($input, Equipe::$rules);

        if ($validation->passes()) {
            /*
             * Movendo o arquivo para o diretório correto
             */

            $arquivo = Input::hasFile('logotipo') ? Input::file('logotipo')
                : null;

            if (isset($arquivo) && $arquivo->isValid()) {
                $destinationPath = 'uploads/';
                $fileName = 'equipe_' . str_replace('.', '', microtime(true)) . '.' . $arquivo->getClientOriginalExtension();
                $arquivo->move($destinationPath, $fileName);
                $input['logotipo'] = $fileName;
            }

            $equipe = $this->equipe->find($id);

            $equipe->update($input);

            return Response::json(array('success' => true));
        }

        return Response::json(array('success' => false,
            'errors' => $validation->getMessageBag()->all(),
            'message' => 'There were validation errors.'), 300);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        Equipe::destroy($id);

        return Response::json(array('success' => true));
    }
}
