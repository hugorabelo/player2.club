@extends('layouts.scaffold')

@section('main')

<h1>{{ trans('messages.usuarioFases_list') }}</h1>

<p>{{ link_to_route('usuarioFases.create', trans('messages.usuarioFase_add'), null, array('class' => 'btn btn-lg btn-success')) }}</p>

@if ($usuarioFases->count())
	<table class="table table-striped">
		<thead>
			<tr>
				<th>{{ trans('fields.campeonatos_usuarios_id') }}</th>
				<th>{{ trans('fields.campeonato_fases_id') }}</th>
				<th>&nbsp;</th>
			</tr>
		</thead>

		<tbody>
			@foreach ($usuarioFases as $usuarioFase)
				<tr>
					<td>{{{ $usuarioFase->campeonatos_usuarios_id }}}</td>
					<td>{{{ $usuarioFase->campeonato_fases_id }}}</td>
                    <td>
                        {{ Form::open(array('style' => 'display: inline-block;', 'method' => 'DELETE', 'route' => array('usuarioFases.destroy', $usuarioFase->id))) }}
                            {{ Form::submit(trans('fields.delete'), array('class' => 'btn btn-danger')) }}
                        {{ Form::close() }}
                        {{ link_to_route('usuarioFases.edit', trans('fields.edit'), array($usuarioFase->id), array('class' => 'btn btn-info')) }}
                    </td>
				</tr>
			@endforeach
		</tbody>
	</table>
@else
	{{ trans('messages.usuarioFase_empty') }}
@endif

@stop
