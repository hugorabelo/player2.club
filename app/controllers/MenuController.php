<?php

class MenuController extends BaseController {

	/**
	 * Menu Repository
	 *
	 * @var Menu
	 */
	protected $menu;

	public function __construct(Menu $menu)
	{
		$this->menu = $menu;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index() {
		$menus = Menu::get();
		foreach($menus as $menu) {
			if($menu->pai() != null) {
				$menu->pai = $menu->pai()->descricao;
			}
		}
		return Response::json($menus);
	}

	/**
	 * Exibe os menus organizados em forma de árvore entre pais e filhos
	 *
	 * @return Response
	 */
	public function getMenuTree() {
		$menus = Menu::whereNull('menu_pai')->get();
		foreach($menus as $menuRaiz) {
			$menuRaiz->items = $this->getMenusFilhos($menuRaiz->id);
		}
		return Response::json($menus);
	}

	public function getMenusFilhos($id) {
		$items = array();
		$menusFilhos = Menu::where('menu_pai', '=', $id)->get();
		foreach($menusFilhos as $menuFilho) {
			$menuFilho->items = $this->getMenusFilhos($menuFilho->id);
			array_push($items, $menuFilho);
		}
		return $items;
	}

	public function create()
	{
		$menuPais = Menu::get();
		return Response::json(compact('menuPais'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store() {
		$input = Input::all();
		$validation = Validator::make($input, Menu::$rules);

		if ($validation->passes())
		{
			Menu::create(Input::all());

			return Response::json(array('success'=>true));
		}

		return Response::json(array('success'=>false,
			'errors'=>$validation->getMessageBag()->all(),
			'message'=>'There were validation errors.'),300);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
    public function edit($id)
    {
		$menu = Menu::find($id);
		$menuPais = Menu::get()->except($id);

        return Response::json(compact('menu', 'menuPais'));
    }

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$input = array_except(Input::all(), '_method');
		$validation = Validator::make($input, Menu::$rules);

		if ($validation->passes())
		{
			$menu = $this->menu->find($id);
			$menu->update($input);

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
	public function destroy($id) {
		Menu::destroy($id);

		return Response::json(array('success'=>true));
	}

}
