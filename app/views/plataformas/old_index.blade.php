@extends('layouts.scaffold')

@section('main')

<p>{{ link_to_route('plataformas.create', trans('messages.plataforma_add'), null, array('class' => 'btn btn-lg btn-success')) }}</p>

<div class="panel panel-primary">
	<div class="panel-heading">
		<h3 class="panel-title">{{ trans('messages.plataforma_list') }}</h3>
	</div>

@if ($plataformas->count())
	<table class="table">
		<thead>
			<tr>
				<th>{{ trans('fields.descricao') }}</th>
				<th>{{ trans('fields.logomarca') }}</th>
				<th>&nbsp;</th>
			</tr>
		</thead>

		<tbody>
			@foreach ($plataformas as $plataforma)
				<tr>
					<td>{{{ $plataforma->descricao }}}</td>
					<td><img src="uploads/{{ $plataforma->imagem_logomarca }}" height="40"/></td>
                    <td>
                        {{ Form::open(array('style' => 'display: inline-block;', 'method' => 'DELETE', 'route' => array('plataformas.destroy', $plataforma->id))) }}
                            {{ Form::submit(trans('fields.delete'), array('class' => 'btn btn-danger')) }}
                        {{ Form::close() }}
                        {{ link_to_route('plataformas.edit', trans('fields.edit'), array($plataforma->id), array('class' => 'btn btn-info')) }}
                    </td>
				</tr>
			@endforeach
		</tbody>
	</table>
@else
    <div class="alert alert-info">
        {{ trans('messages.plataforma_empty') }}
    </div>
@endif

</div>

@stop

@section('custom_script')
    <script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.3.3/angular.min.js"></script>
@stop
