@extends('layouts.scaffold')

@section('main')

<h1>Show CampeonatoAdmin</h1>

<p>{{ link_to_route('campeonatoAdmins.index', 'Return to All campeonatoAdmins', null, array('class'=>'btn btn-lg btn-primary')) }}</p>

<table class="table table-striped">
	<thead>
		<tr>
			<th>{{ trans('fields.users_id') }}</th>
				<th>{{ trans('fields.campeonatos_id') }}</th>
		</tr>
	</thead>

	<tbody>
		<tr>
			<td>{{{ $campeonatoAdmin->users_id }}}</td>
					<td>{{{ $campeonatoAdmin->campeonatos_id }}}</td>
                    <td>
                        {{ Form::open(array('style' => 'display: inline-block;', 'method' => 'DELETE', 'route' => array('campeonatoAdmins.destroy', $campeonatoAdmin->id))) }}
                            {{ Form::submit(trans('fields.delete'), array('class' => 'btn btn-danger')) }}
                        {{ Form::close() }}
                        {{ link_to_route('campeonatoAdmins.edit', trans('fields.edit'), array($campeonatoAdmin->id), array('class' => 'btn btn-info')) }}
                    </td>
		</tr>
	</tbody>
</table>

@stop
