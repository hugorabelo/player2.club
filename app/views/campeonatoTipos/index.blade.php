@extends('layouts.scaffold')

@section('main')

<h1>{{ trans('messages.campeonatoTipo_list') }}</h1>

<p>{{ link_to_route('campeonatoTipos.create', trans('messages.campeonatoTipo_add'), null, array('class' => 'btn btn-lg btn-success')) }}</p>

@if ($campeonatoTipos->count())
	<table class="table table-striped">
		<thead>
			<tr>
				<th>{{ trans('fields.descricao') }}</th>
				<th>{{ trans('fields.maximo_jogadores_partida') }}</th>
				<th>&nbsp;</th>
			</tr>
		</thead>

		<tbody>
			@foreach ($campeonatoTipos as $campeonatoTipo)
				<tr>
					<td>{{{ $campeonatoTipo->descricao }}}</td>
					<td>{{{ $campeonatoTipo->maximo_jogadores_partida }}}</td>
                    <td>
                        {{ Form::open(array('style' => 'display: inline-block;', 'method' => 'DELETE', 'route' => array('campeonatoTipos.destroy', $campeonatoTipo->id))) }}
                            {{ Form::submit(trans('fields.delete'), array('class' => 'btn btn-danger')) }}
                        {{ Form::close() }}
                        {{ link_to_route('campeonatoTipos.edit', trans('fields.edit'), array($campeonatoTipo->id), array('class' => 'btn btn-info')) }}
                    </td>
				</tr>
			@endforeach
		</tbody>
	</table>
@else
	{{ trans('messages.campeonatoTipo_empty') }}
@endif


@stop
