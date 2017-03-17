<?php

class CampeonatoAdminsController extends Controller {

	/**
	 * CampeonatoAdmin Repository
	 *
	 * @var CampeonatoAdmin
	 */
	protected $campeonatoAdmin;

	public function __construct(CampeonatoAdmin $campeonatoAdmin)
	{
		$this->campeonatoAdmin = $campeonatoAdmin;
	}

	public function index() {
		return null;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function show($id)
	{
		$campeonatoAdmins = CampeonatoAdmin::where('campeonatos_id','=',$id)->get();
		foreach($campeonatoAdmins as $admin) {
			$admin->usuario = $admin->usuario();
		}
		return Response::json($campeonatoAdmins);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$input = Input::all();
		$validation = Validator::make($input, CampeonatoAdmin::$rules);

		if ($validation->passes())
		{
			$this->campeonatoAdmin->create($input);

			return Response::json(array('success'=>true));
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
		$this->campeonatoAdmin->find($id)->delete();

		return Response::json(array('success'=>true));
	}

}
