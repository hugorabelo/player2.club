@extends('layouts.scaffold')

@section('main')

<h1>{{ trans('messages.usuarioTipo_list') }}</h1>

<p>{{ link_to_route('usuarioTipos.create', trans('messages.usuarioTipo_add'), null, array('class' => 'btn btn-lg btn-success')) }}</p>

@if ($usuarioTipos->count())
	<table class="table table-striped">
		<thead>
			<tr>
				<th>{{ trans('fields.descricao') }}</th>
				<th>{{ trans('fields.prioridade') }}</th>
				<th>&nbsp;</th>
			</tr>
		</thead>

		<tbody>
			@foreach ($usuarioTipos as $usuarioTipo)
				<tr>
					<td>{{{ $usuarioTipo->descricao }}}</td>
					<td>{{{ $usuarioTipo->prioridade }}}</td>
                    <td>
                        {{ Form::open(array('style' => 'display: inline-block;', 'method' => 'DELETE', 'route' => array('usuarioTipos.destroy', $usuarioTipo->id))) }}
                            {{ Form::submit(trans('fields.delete'), array('class' => 'btn btn-danger')) }}
                        {{ Form::close() }}
                        {{ link_to_route('usuarioTipos.edit', trans('fields.edit'), array($usuarioTipo->id), array('class' => 'btn btn-info link')) }}
                    </td>
				</tr>
			@endforeach
		</tbody>
	</table>
@else
	{{ trans('messages.plataforma_empty') }}
@endif

@stop
