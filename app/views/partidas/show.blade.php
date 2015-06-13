@extends('layouts.scaffold')

@section('main')

<h1>Show Partida</h1>

<p>{{ link_to_route('partidas.index', 'Return to All partidas', null, array('class'=>'btn btn-lg btn-primary')) }}</p>

<table class="table table-striped">
	<thead>
		<tr>
			<th>Data_realizacao</th>
				<th>Fase_grupos_id</th>
				<th>Rodada</th>
		</tr>
	</thead>

	<tbody>
		<tr>
			<td>{{{ $partida->data_realizacao }}}</td>
					<td>{{{ $partida->fase_grupos_id }}}</td>
					<td>{{{ $partida->rodada }}}</td>
                    <td>
                        {{ Form::open(array('style' => 'display: inline-block;', 'method' => 'DELETE', 'route' => array('partidas.destroy', $partida->id))) }}
                            {{ Form::submit('Delete', array('class' => 'btn btn-danger')) }}
                        {{ Form::close() }}
                        {{ link_to_route('partidas.edit', 'Edit', array($partida->id), array('class' => 'btn btn-info')) }}
                    </td>
		</tr>
	</tbody>
</table>

@stop
