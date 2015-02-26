@extends('layouts.scaffold')

@section('main')

<h1>Show User</h1>

<p>{{ link_to_route('users.index', 'Return to All users', null, array('class'=>'btn btn-lg btn-primary')) }}</p>

<table class="table table-striped">
	<thead>
		<tr>
			<th>{{ trans('fields.nome') }}</th>
				<th>{{ trans('fields.email') }}</th>
				<th>{{ trans('fields.password') }}</th>
				<th>{{ trans('fields.gamertag_live') }}</th>
				<th>{{ trans('fields.gamertag_psn') }}</th>
				<th>{{ trans('fields.usuarioTipos_id') }}</th>
		</tr>
	</thead>

	<tbody>
		<tr>
			<td>{{{ $user->nome }}}</td>
					<td>{{{ $user->email }}}</td>
					<td>{{{ $user->password }}}</td>
					<td>{{{ $user->gamertag_live }}}</td>
					<td>{{{ $user->gamertag_psn }}}</td>
					<td>{{{ $user->usuario_tipos_id }}}</td>
                    <td>
                        {{ Form::open(array('style' => 'display: inline-block;', 'method' => 'DELETE', 'route' => array('users.destroy', $user->id))) }}
                            {{ Form::submit(trans('fields.delete'), array('class' => 'btn btn-danger')) }}
                        {{ Form::close() }}
                        {{ link_to_route('users.edit', trans('fields.edit'), array($user->id), array('class' => 'btn btn-info')) }}
                    </td>
		</tr>
	</tbody>
</table>

@stop
