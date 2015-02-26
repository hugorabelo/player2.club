@extends('layouts.scaffold')

@section('main')

<div class="col-md-9">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">{{ trans('messages.user_edit') }}</h3>
        </div>
        <div class="panel-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    {{ implode('', $errors->all('<li class="error">:message</li>')) }}
                </ul>
            </div>
        @endif

{{ Form::model($user, array('class' => 'form-horizontal', 'method' => 'PATCH', 'route' => array('users.update', $user->id))) }}

        <div class="form-group">
            {{ Form::label('nome', trans('fields.nome'), array('class'=>'col-md-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::text('nome', Input::old('nome'), array('class'=>'form-control', 'placeholder'=>trans('fields.nome'))) }}
            </div>
        </div>

        <div class="form-group">
            {{ Form::label('email', trans('fields.email'), array('class'=>'col-md-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::text('email', Input::old('email'), array('class'=>'form-control', 'placeholder'=>trans('fields.email'))) }}
            </div>
        </div>

        <div class="form-group">
            {{ Form::label('password', trans('fields.password'), array('class'=>'col-md-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::password('password', array('class'=>'form-control', 'placeholder'=>trans('fields.password'))) }}
            </div>
        </div>

        <div class="form-group">
            {{ Form::label('usuario_tipos_id', trans('fields.usuarioTipos_id'), array('class'=>'col-md-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::select('usuario_tipos_id', $usuarioTipos, null, array('class'=>'form-control')) }}
            </div>
        </div>


<div class="form-group">
    <label class="col-sm-2 control-label">&nbsp;</label>
    <div class="col-sm-10">
      {{ Form::submit(trans('fields.update'), array('class' => 'btn btn-primary')) }}
      {{ link_to_route('users.index', trans('fields.cancel'), $user->id, array('class' => 'btn btn-default')) }}
    </div>
</div>

{{ Form::close() }}
</div>
    </div>
</div>

<div class="col-md-3">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">{{ trans('messages.user_gamertags') }}</h3>
        </div>
        <div class="panel-body">
            <li>Adicionar Administrador</li>
            <li>Fases do Campeonato</li>
            <li>-- Regras de Pontuação</li>
        </div>
    </div>
</div>
@stop
