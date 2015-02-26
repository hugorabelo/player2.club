@extends('layouts.scaffold')

@section('main')

@include('layouts.listHead', array('titulo'=>trans('messages.campeonato_list'), 'link'=>'campeonatos.create', 'mensagem'=>trans('messages.campeonato_add')))

@if ($campeonatos->count())

	<div id="modalConfirma" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
		{{ Form::open(array('id'=>'form_delete', 'method' => 'DELETE', 'route' => array('campeonatos.destroy', 'xxxxxx'))) }}
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
				 <div class="modal-body">
	        		{{ trans('messages.confirma_exclusao') }}
	      		</div>
	      		<div class="modal-footer">
	      			{{ Form::submit(trans('messages.yes'), array('class' => 'btn btn-primary')) }}
	      			<button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('messages.no') }}</button>
	      		</div>
			</div>
		</div>
        {{ Form::close() }}
	</div>

	<table class="table table-striped">
		<thead>
			<tr>
				<th>{{ trans('fields.descricao') }}</th>
				<th>{{ trans('fields.detalhes') }}</th>
				<th>{{ trans('fields.jogos_id') }}</th>
				<th>{{ trans('fields.campeonatotipos_id') }}</th>
				<th>&nbsp;</th>
			</tr>
		</thead>

		<tbody>
			@foreach ($campeonatos as $campeonato)
				<tr>
					<td>{{{ $campeonato->descricao }}}</td>
					<td>{{{ $campeonato->detalhes }}}</td>
					<td>{{{ $campeonato->jogo()->descricao }}}</td>
					<td>{{{ $campeonato->campeonatoTipo()->descricao }}}</td>
                    <td>
	                    {{ Form::button(trans('fields.delete'),
	                    				array('class' => 'btn btn-danger',
	                    					  'onclick'=>'carregaModalExclusao('.$campeonato->id.')')) }}
                        {{ link_to_route('campeonatos.edit', trans('fields.edit'), array($campeonato->id), array('class' => 'btn btn-info link')) }}
                    </td>
				</tr>
			@endforeach
		</tbody>
	</table>
@else
	{{ trans('messages.campeonato_empty') }}
@endif

@stop
