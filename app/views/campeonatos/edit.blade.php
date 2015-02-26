@extends('layouts.scaffold')
@section('main')

<div class="col-md-9">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{{ trans('messages.campeonato_edit') }}</h3>
        </div>
        <div class="panel-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    {{ implode('', $errors->all('<li class="error">:message</li>')) }}
                </ul>
            </div>
        @endif
    {{ Form::model($campeonato, array('class' => 'form-horizontal', 'method' => 'PATCH', 'route' => array('campeonatos.update', $campeonato->id))) }}

            <div class="form-group">
                {{ Form::label('descricao', trans('fields.descricao'), array('class'=>'col-md-2 control-label')) }}
                <div class="col-sm-10">
                  {{ Form::text('descricao', Input::old('descricao'), array('class'=>'form-control', 'placeholder'=>trans('fields.descricao'))) }}
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
                  {{ Form::select('plataformas_id', $plataformas, Input::old('plataformas_id'), array('class'=>'form-control')) }}
                </div>
            </div>

            <div class="form-group">
                {{ Form::label('jogos_id', trans('fields.jogos_id'), array('class'=>'col-md-2 control-label')) }}
                <div class="col-sm-10">
                  {{ Form::select('jogos_id', $jogos, Input::old('jogos_id'), array('class'=>'form-control')) }}
                </div>
            </div>

            <div class="form-group">
                {{ Form::label('campeonato_tipos_id', trans('fields.campeonatotipos_id'), array('class'=>'col-md-2 control-label')) }}
                <div class="col-sm-10">
                  {{ Form::select('campeonato_tipos_id', $campeonatoTipo, Input::old('campeonatotipos_id'), array('class'=>'form-control')) }}
                </div>
            </div>


    <div class="form-group">
        <label class="col-sm-2 control-label">&nbsp;</label>
        <div class="col-sm-10">
          {{ Form::submit(trans('fields.update'), array('class' => 'btn btn-primary')) }}
          {{ link_to_route('campeonatos.index', trans('fields.cancel'), $campeonato->id, array('class' => 'btn btn-default')) }}
        </div>
    </div>

    {{ Form::close() }}
</div>
    </div>
</div>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
	    <div class="modal-content">
	        <div class="modal-body" id="carrega_modal">
            </div>
	    </div>
	</div>
</div>

<div class="col-md-3">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">Opções do Campeonato</h3>
        </div>
        <div class="panel-body">
            <li>Adicionar Administrador</li>
            <li>Fases do Campeonato</li>
            <li>-- Regras de Pontuação</li>
        </div>
    </div>

<div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
            Administradores
        </a>
      </h4>
    </div>
    <div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
      <div class="panel-body">
        <button type="button" class="btn btn-sm btn-block demo" id="botaoModal">
            Adicionar Novo
        </button>
        <div id="lista_administradores">

        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-info">
    <div class="panel-heading" role="tab" id="headingTwo">
      <h4 class="panel-title">
        <a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
          Fases
        </a>
      </h4>
    </div>
    <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
      <div class="panel-body">
          {{ $campeonato->administradores() }}
        <li>Primeira Faase</li>
        <li>Semi Final</li>
        <li>Final</li>
      </div>
    </div>
  </div>
</div>

@stop

@section('custom_script')
    <script id="ajax" type="text/javascript">
        $(document).ready(function() {
            $('#lista_administradores').load('{{ URL::to("campeonatoAdmins/{$campeonato->id}") }}');

            $("#botaoModal").click(function(e) {
                e.preventDefault();
                $('#carrega_modal').load('{{ URL::to("campeonatoAdmins/create/{$campeonato->id}") }}');
                $('#myModal').modal();
            });

            $("#carrega_modal").on("click", '#botao_cancelar', function(e) {
                e.preventDefault();
                $('#myModal').modal('hide');
            });

//            $("#carrega_modal").on("click", '#botao_confirmar', function(e) {
//                e.preventDefault();
//                alert('confirma');
//            });
        });
    </script>
@stop
