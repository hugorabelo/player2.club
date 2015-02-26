@extends('layouts.scaffold')

@section('main')

<div class="row">
    <div class="col-md-10 col-md-offset-2">
        <h1>{{ trans('messages.user_create') }}</h1>

        @if ($errors->any())
        	<div class="alert alert-danger">
        	    <ul>
                    {{ implode('', $errors->all('<li class="error">:message</li>')) }}
                </ul>
        	</div>
        @endif
    </div>
</div>

{{ Form::open(array('route' => 'users.store', 'class' => 'form-horizontal')) }}

        <div class="form-group">
            {{ Form::label('nome', trans('fields.nome'), array('class'=>'col-md-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::text('nome', Input::old('nome'), array('class'=>'form-control', 'placeholder'=>trans('fields.nome'))) }}
            </div>
        </div>

        <div class="form-group">
            {{ Form::label('email', trans('fields.email'), array('class'=>'col-md-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::email('email', Input::old('email'), array('class'=>'form-control', 'placeholder'=>trans('fields.email'))) }}
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
      {{ Form::submit(trans('fields.create'), array('class' => 'btn btn-lg btn-primary')) }}
      {{ link_to_route('users.index', trans('fields.cancel'), '', array('class' => 'btn btn-lg btn-default')) }}
    </div>
</div>

{{ Form::close() }}

@stop


