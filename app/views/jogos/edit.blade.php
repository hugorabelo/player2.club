@extends('layouts.scaffold')

@section('main')

<div class="col-md-9">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">{{ trans('messages.jogo_edit') }}</h3>
        </div>
        <div class="panel-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    {{ implode('', $errors->all('<li class="error">:message</li>')) }}
                </ul>
            </div>
        @endif

{{ Form::model($jogo, array('class' => 'form-horizontal', 'files'=>'true', 'method' => 'PATCH', 'route' => array('jogos.update', $jogo->id))) }}

        <div class="form-group">
            {{ Form::label('descricao', trans('fields.descricao'), array('class'=>'col-md-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::text('descricao', Input::old('descricao'), array('class'=>'form-control', 'placeholder'=>trans('fields.descricao'))) }}
            </div>
        </div>

        <div class="form-group">
            {{ Form::label('imagem_capa', trans('fields.imagem_capa'), array('class'=>'col-md-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::file('imagem_capa', array('class'=>'form-control')) }}
            </div>
        </div>

<div class="form-group">
    <label class="col-sm-2 control-label">&nbsp;</label>
    <div class="col-sm-10">
      {{ Form::submit(trans('fields.update'), array('class' => 'btn btn-primary')) }}
      {{ link_to_route('jogos.index', trans('fields.cancel'), $jogo->id, array('class' => 'btn btn-default')) }}
    </div>
</div>

{{ Form::close() }}

</div>
    </div>
</div>

@stop
