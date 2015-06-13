@extends('layouts.scaffold')

@section('main')

<div class="row">
    <div class="col-md-10 col-md-offset-2">
        <h1>{{ trans('messages.usuarioGrupo_edit') }}</h1>

        @if ($errors->any())
        	<div class="alert alert-danger">
        	    <ul>
                    {{ implode('', $errors->all('<li class="error">:message</li>')) }}
                </ul>
        	</div>
        @endif
    </div>
</div>

{{ Form::model($usuarioGrupo, array('class' => 'form-horizontal', 'method' => 'PATCH', 'route' => array('usuarioGrupos.update', $usuarioGrupo->id))) }}

        <div class="form-group">
            {{ Form::label('users_id', trans('fields.users_id'), array('class'=>'col-md-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::input('number', 'users_id', Input::old('users_id'), array('class'=>'form-control')) }}
            </div>
        </div>

        <div class="form-group">
            {{ Form::label('fase_grupos_id', trans('fields.fase_grupos_id'), array('class'=>'col-md-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::input('number', 'fase_grupos_id', Input::old('fase_grupos_id'), array('class'=>'form-control')) }}
            </div>
        </div>


<div class="form-group">
    <label class="col-sm-2 control-label">&nbsp;</label>
    <div class="col-sm-10">
      {{ Form::submit(trans('fields.update'), array('class' => 'btn btn-lg btn-primary')) }}
      {{ link_to_route('usuarioGrupos.index', trans('fields.cancel'), $usuarioGrupo->id, array('class' => 'btn btn-lg btn-default')) }}
    </div>
</div>

{{ Form::close() }}

@stop
