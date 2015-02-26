@extends('layouts.scaffold')

@section('main')

<h1>{{ trans('messages.user_list') }}</h1>

<p>{{ link_to_route('users.create', trans('messages.user_add'), null, array('class' => 'btn btn-lg btn-success')) }}</p>

@if ($users->count())
	<table class="table table-striped">
		<thead>
			<tr>
				<th>{{ trans('fields.nome') }}</th>
				<th>{{ trans('fields.email') }}</th>
				<th>{{ trans('fields.usuarioTipos_id') }}</th>
				<th>&nbsp;</th>
			</tr>
		</thead>

		<tbody>
			@foreach ($users as $user)
				<tr>
					<td>{{{ $user->nome }}}</td>
					<td>{{{ $user->email }}}</td>
					<td>{{{ $user->usuarioTipo()->descricao }}}</td>
                    <td>
                        {{ Form::open(array('style' => 'display: inline-block;', 'method' => 'DELETE', 'route' => array('users.destroy', $user->id))) }}
                            {{ Form::submit(trans('fields.delete'), array('class' => 'btn btn-danger')) }}
                        {{ Form::close() }}
                        {{ link_to_route('users.edit', trans('fields.edit'), array($user->id), array('class' => 'btn btn-info')) }}
                    </td>
				</tr>
			@endforeach
		</tbody>
	</table>
@else
	{{ trans('messages.user_empty') }}
@endif

@stop
