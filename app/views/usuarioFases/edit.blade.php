@extends('layouts.scaffold')

@section('main')

<div class="row">
    <div class="col-md-10 col-md-offset-2">
        <h1>{{ trans('messages.usuarioFase_edit') }}</h1>

        @if ($errors->any())
        	<div class="alert alert-danger">
        	    <ul>
                    {{ implode('', $errors->all('<li class="error">:message</li>')) }}
                </ul>
        	</div>
        @endif
    </div>
</div>

{{ Form::model($usuarioFase, array('class' => 'form-horizontal', 'method' => 'PATCH', 'route' => array('usuarioFases.update', $usuarioFase->id))) }}

        <div class="form-group">
            {{ Form::label('campeonatos_usuarios_id', trans('fields.campeonatos_usuarios_id'), array('class'=>'col-md-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::input('number', 'campeonatos_usuarios_id', Input::old('campeonatos_usuarios_id'), array('class'=>'form-control')) }}
            </div>
        </div>

        <div class="form-group">
            {{ Form::label('campeonato_fases_id', trans('fields.campeonato_fases_id'), array('class'=>'col-md-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::input('number', 'campeonato_fases_id', Input::old('campeonato_fases_id'), array('class'=>'form-control')) }}
            </div>
        </div>


<div class="form-group">
    <label class="col-sm-2 control-label">&nbsp;</label>
    <div class="col-sm-10">
      {{ Form::submit(trans('fields.update'), array('class' => 'btn btn-lg btn-primary')) }}
      {{ link_to_route('usuarioFases.index', trans('fields.cancel'), $usuarioFase->id, array('class' => 'btn btn-lg btn-default')) }}
    </div>
</div>

{{ Form::close() }}

@stop
