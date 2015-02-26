@if ($campeonatoAdmins->count())
	<table class="table">
		<tbody>
			@foreach ($campeonatoAdmins as $campeonatoAdmin)
				<tr>
					<td>{{{ $campeonatoAdmin->nomeUsuario() }}}</td>
                    <td>
                        {{ link_to_route('campeonatoAdmins.destroy', '', array($campeonatoAdmin->id), array('class' => 'btn btn-danger btn-xs glyphicon glyphicon-trash')) }}
                    </td>
				</tr>
			@endforeach
		</tbody>
	</table>
@else
	{{ trans('messages.campeonatoAdmin_empty') }}
@endif
