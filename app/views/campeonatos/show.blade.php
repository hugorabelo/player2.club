@extends('layouts.scaffold')

@section('main')

<h1>Show Campeonato</h1>

<p>{{ link_to_route('campeonatos.index', 'Return to All campeonatos', null, array('class'=>'btn btn-lg btn-primary')) }}</p>

<table class="table table-striped">
	<thead>
		<tr>
			<th>{{ trans('fields.descricao') }}</th>
				<th>{{ trans('fields.detalhes') }}</th>
				<th>{{ trans('fields.jogos_id') }}</th>
				<th>{{ trans('fields.campeonatotipos_id') }}</th>
		</tr>
	</thead>

	<tbody>
		<tr>
			<td>{{{ $campeonato->descricao }}}</td>
					<td>{{{ $campeonato->detalhes }}}</td>
					<td>{{{ $campeonato->jogos_id }}}</td>
					<td>{{{ $campeonato->campeonatotipos_id }}}</td>
                    <td>
                        {{ Form::open(array('style' => 'display: inline-block;', 'method' => 'DELETE', 'route' => array('campeonatos.destroy', $campeonato->id))) }}
                            {{ Form::submit(trans('fields.delete'), array('class' => 'btn btn-danger')) }}
                        {{ Form::close() }}
                        {{ link_to_route('campeonatos.edit', trans('fields.edit'), array($campeonato->id), array('class' => 'btn btn-info')) }}
                    </td>
		</tr>
	</tbody>
</table>

@stop
