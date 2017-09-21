<?php


class TutorialItemController extends Controller
{
    /**
     * TutorialItem Repository
     *
     * @var TutorialItem
     */
    protected $tutorialItem;

    public function __construct(TutorialItem $tutorialItem)
    {
        $this->tutorialItem = $tutorialItem;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return Response::json(TutorialItem::get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $input = Input::all();
        $validation = Validator::make($input, TutorialItem::$rules);

        if ($validation->passes())
        {
            TutorialItem::create($input);

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
        return Response::json(TutorialItem::find($id));
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
        $validation = Validator::make($input, TutorialItem::$rules);

        if ($validation->passes())
        {
            $tutorialItem = $this->tutorialItem->find($id);
            $tutorialItem->update($input);

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
        TutorialItem::destroy($id);

        return Response::json(array('success'=>true));
    }
}
