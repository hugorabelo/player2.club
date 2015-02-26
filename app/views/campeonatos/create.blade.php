@extends('layouts.scaffold')

@section('main')

<div class="row">
    <div class="col-md-10 col-md-offset-2">
        <h1>{{ trans('messages.campeonato_create') }}</h1>

        @if ($errors->any())
        	<div class="alert alert-danger">
        	    <ul>
                    {{ implode('', $errors->all('<li class="error">:message</li>')) }}
                </ul>
        	</div>
        @endif
    </div>
</div>

{{ Form::open(array('route' => 'campeonatos.store', 'class' => 'form-horizontal')) }}

        <div class="form-group">
            {{ Form::label('descricao', trans('fields.descricao'), array('class'=>'col-md-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::text('descricao', Input::old('descricao'), array('class'=>'form-control', 'placeholder'=>trans('fields.descricao'), 'data-validation'=>'required')) }}
            </div>
        </div>

        <div class="form-group">
            {{ Form::label('detalhes', trans('fields.detalhes'), array('class'=>'col-md-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::textarea('detalhes', Input::old('detalhes'), array('class'=>'form-control', 'placeholder'=>trans('fields.detalhes'))) }}
            </div>
        </div>

        <div class="form-group">
            {{ Form::label('plataformas_id', trans('fields.plataformas_id'), array('class'=>'col-md-2 control-label')) }}
            <div class="col-sm-10">
                {{ Form::select('plataformas_id', $plataformas, null, array('class'=>'form-control')) }}
            </div>
        </div>

        <div class="form-group">
            {{ Form::label('jogos_id', trans('fields.jogos_id'), array('class'=>'col-md-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::select('jogos_id', $jogos, null, array('class'=>'form-control')) }}
            </div>
        </div>

        <div class="form-group">
            {{ Form::label('campeonato_tipos_id', trans('fields.campeonatotipos_id'), array('class'=>'col-md-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::select('campeonato_tipos_id', $campeonatoTipo, null, array('class'=>'form-control')) }}
            </div>
        </div>


<div class="form-group">
    <label class="col-sm-2 control-label">&nbsp;</label>
    <div class="col-sm-10">
      {{ Form::submit(trans('fields.create'), array('class' => 'btn btn-lg btn-primary')) }}
      {{ link_to_route('campeonatos.index', trans('fields.cancel'), '', array('class' => 'btn btn-lg btn-default')) }}
    </div>
</div>

{{ Form::close() }}

@stop
