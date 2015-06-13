@extends('layouts.scaffold')

@section('main')

<h1>Show UsuarioGrupo</h1>

<p>{{ link_to_route('usuarioGrupos.index', 'Return to All usuarioGrupos', null, array('class'=>'btn btn-lg btn-primary')) }}</p>

<table class="table table-striped">
	<thead>
		<tr>
			<th>{{ trans('fields.users_id') }}</th>
				<th>{{ trans('fields.fase_grupos_id') }}</th>
		</tr>
	</thead>

	<tbody>
		<tr>
			<td>{{{ $usuarioGrupo->users_id }}}</td>
					<td>{{{ $usuarioGrupo->fase_grupos_id }}}</td>
                    <td>
                        {{ Form::open(array('style' => 'display: inline-block;', 'method' => 'DELETE', 'route' => array('usuarioGrupos.destroy', $usuarioGrupo->id))) }}
                            {{ Form::submit(trans('fields.delete'), array('class' => 'btn btn-danger')) }}
                        {{ Form::close() }}
                        {{ link_to_route('usuarioGrupos.edit', trans('fields.edit'), array($usuarioGrupo->id), array('class' => 'btn btn-info')) }}
                    </td>
		</tr>
	</tbody>
</table>

@stop
