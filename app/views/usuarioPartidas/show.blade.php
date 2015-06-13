@extends('layouts.scaffold')

@section('main')

<h1>Show UsuarioPartida</h1>

<p>{{ link_to_route('usuarioPartidas.index', 'Return to All usuarioPartidas', null, array('class'=>'btn btn-lg btn-primary')) }}</p>

<table class="table table-striped">
	<thead>
		<tr>
			<th>Posicao</th>
				<th>Placar</th>
				<th>Pontuacao</th>
				<th>Data_placar</th>
				<th>Partidas_id</th>
				<th>Users_id</th>
		</tr>
	</thead>

	<tbody>
		<tr>
			<td>{{{ $usuarioPartida->posicao }}}</td>
					<td>{{{ $usuarioPartida->placar }}}</td>
					<td>{{{ $usuarioPartida->pontuacao }}}</td>
					<td>{{{ $usuarioPartida->data_placar }}}</td>
					<td>{{{ $usuarioPartida->partidas_id }}}</td>
					<td>{{{ $usuarioPartida->users_id }}}</td>
                    <td>
                        {{ Form::open(array('style' => 'display: inline-block;', 'method' => 'DELETE', 'route' => array('usuarioPartidas.destroy', $usuarioPartida->id))) }}
                            {{ Form::submit('Delete', array('class' => 'btn btn-danger')) }}
                        {{ Form::close() }}
                        {{ link_to_route('usuarioPartidas.edit', 'Edit', array($usuarioPartida->id), array('class' => 'btn btn-info')) }}
                    </td>
		</tr>
	</tbody>
</table>

@stop
