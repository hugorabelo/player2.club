@extends('layouts.scaffold')

@section('main')

<div class="row">
    <div class="col-md-10 col-md-offset-2">
        <h1>{{ trans('messages.campeonatoTipo_edit') }}</h1>

        @if ($errors->any())
        	<div class="alert alert-danger">
        	    <ul>
                    {{ implode('', $errors->all('<li class="error">:message</li>')) }}
                </ul>
        	</div>
        @endif
    </div>
</div>
{{ Form::model($campeonatoTipo, array('class' => 'form-horizontal', 'method' => 'PATCH', 'route' => array('campeonatoTipos.update', $campeonatoTipo->id))) }}

        <div class="form-group">
            {{ Form::label('descricao', trans('fields.descricao'), array('class'=>'col-md-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::text('descricao', Input::old('descricao'), array('class'=>'form-control', 'placeholder'=>trans('fields.descricao'))) }}
            </div>
        </div>

        <div class="form-group">
            {{ Form::label('maximo_jogadores_partida', trans('fields.maximo_jogadores_partida'), array('class'=>'col-md-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::input('number', 'maximo_jogadores_partida', Input::old('maximo_jogadores_partida'), array('class'=>'form-control')) }}
            </div>
        </div>


<div class="form-group">
    <label class="col-sm-2 control-label">&nbsp;</label>
    <div class="col-sm-10">
      {{ Form::submit(trans('fields.update'), array('class' => 'btn btn-lg btn-primary')) }}
      {{ link_to_route('campeonatoTipos.index', trans('fields.cancel'), $campeonatoTipo->id, array('class' => 'btn btn-lg btn-default')) }}
    </div>
</div>

{{ Form::close() }}

@stop
