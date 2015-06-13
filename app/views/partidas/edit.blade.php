@extends('layouts.scaffold')

@section('main')

<div class="row">
    <div class="col-md-10 col-md-offset-2">
        <h1>Edit Partida</h1>

        @if ($errors->any())
        	<div class="alert alert-danger">
        	    <ul>
                    {{ implode('', $errors->all('<li class="error">:message</li>')) }}
                </ul>
        	</div>
        @endif
    </div>
</div>

{{ Form::model($partida, array('class' => 'form-horizontal', 'method' => 'PATCH', 'route' => array('partidas.update', $partida->id))) }}

        <div class="form-group">
            {{ Form::label('data_realizacao', 'Data_realizacao:', array('class'=>'col-md-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::text('data_realizacao', Input::old('data_realizacao'), array('class'=>'form-control', 'placeholder'=>'Data_realizacao')) }}
            </div>
        </div>

        <div class="form-group">
            {{ Form::label('fase_grupos_id', 'Fase_grupos_id:', array('class'=>'col-md-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::input('number', 'fase_grupos_id', Input::old('fase_grupos_id'), array('class'=>'form-control')) }}
            </div>
        </div>

        <div class="form-group">
            {{ Form::label('rodada', 'Rodada:', array('class'=>'col-md-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::input('number', 'rodada', Input::old('rodada'), array('class'=>'form-control')) }}
            </div>
        </div>


<div class="form-group">
    <label class="col-sm-2 control-label">&nbsp;</label>
    <div class="col-sm-10">
      {{ Form::submit('Update', array('class' => 'btn btn-lg btn-primary')) }}
      {{ link_to_route('partidas.show', 'Cancel', $partida->id, array('class' => 'btn btn-lg btn-default')) }}
    </div>
</div>

{{ Form::close() }}

@stop
