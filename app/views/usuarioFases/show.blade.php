@extends('layouts.scaffold')

@section('main')

<h1>Show UsuarioFase</h1>

<p>{{ link_to_route('usuarioFases.index', 'Return to All usuarioFases', null, array('class'=>'btn btn-lg btn-primary')) }}</p>

<table class="table table-striped">
	<thead>
		<tr>
			<th>{{ trans('fields.campeonatos_usuarios_id') }}</th>
				<th>{{ trans('fields.campeonato_fases_id') }}</th>
		</tr>
	</thead>

	<tbody>
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
	</tbody>
</table>

@stop
