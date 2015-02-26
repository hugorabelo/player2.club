@extends('layouts.scaffold')

@section('main')

@include('layouts.listHead', array('titulo'=>trans('messages.jogo_list'), 'link'=>'jogos.create', 'mensagem'=>trans('messages.jogo_add')))

@if ($jogos->count())
	<div id="modalConfirma" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
		{{ Form::open(array('id'=>'form_delete', 'method' => 'DELETE', 'route' => array('jogos.destroy', 'xxxxxx'))) }}
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

	<table class="table table-bordered">
		<thead>
			<tr class="info">
				<th>{{ trans('fields.descricao') }}</th>
				<th>{{ trans('fields.imagem_capa') }}</th>
				<th>&nbsp;</th>
			</tr>
		</thead>

		<tbody>
			@foreach ($jogos as $jogo)
				<tr>
					<td>{{{ $jogo->descricao }}}</td>
					<td><img src="uploads/{{ $jogo->imagem_capa }}" height="40" /></td>
                    <td>
                        {{ Form::button(trans('fields.delete'),
	                    				array('class' => 'btn btn-danger',
	                    					  'onclick'=>'carregaModalExclusao('.$jogo->id.')')) }}
                        {{ link_to_route('jogos.edit', trans('fields.edit'), array($jogo->id), array('class' => 'btn btn-info')) }}
                    </td>
				</tr>
			@endforeach
		</tbody>
	</table>
@else
	{{ trans('messages.jogo_empty') }}
@endif


@stop
