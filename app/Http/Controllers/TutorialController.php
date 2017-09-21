<?php

class TutorialController extends Controller
{
    /**
     * Tutorial Repository
     *
     * @var tutorial
     */
    protected $tutorial;

    public function __construct(Tutorial $tutorial)
    {
        $this->tutorial = $tutorial;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return Response::json(Tutorial::get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $input = Input::all();
        $validation = Validator::make($input, Tutorial::$rules);

        if ($validation->passes())
        {
            Tutorial::create($input);

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
        return Response::json(Tutorial::find($id));
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
        $validation = Validator::make($input, Tutorial::$rules);

        if ($validation->passes())
        {
            $tutorial = $this->tutorial->find($id);
            $tutorial->update($input);

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
        Tutorial::destroy($id);

        return Response::json(array('success'=>true));
    }
}
