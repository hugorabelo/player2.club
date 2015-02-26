<div class="row">
    <div class="col-md-10 col-md-offset-2">
        <h1>{{ trans('messages.campeonatoAdmin_edit') }}</h1>

        @if ($errors->any())
        	<div class="alert alert-danger">
        	    <ul>
                    {{ implode('', $errors->all('<li class="error">:message</li>')) }}
                </ul>
        	</div>
        @endif
    </div>
</div>

{{ Form::model($campeonatoAdmin, array('class' => 'form-horizontal', 'method' => 'PATCH', 'route' => array('campeonatoAdmins.update', $campeonatoAdmin->id))) }}

        <div class="form-group">
            {{ Form::label('users_id', trans('fields.users_id'), array('class'=>'col-md-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::input('number', 'users_id', Input::old('users_id'), array('class'=>'form-control')) }}
            </div>
        </div>

        <div class="form-group">
            {{ Form::label('campeonatos_id', trans('fields.campeonatos_id'), array('class'=>'col-md-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::input('number', 'campeonatos_id', Input::old('campeonatos_id'), array('class'=>'form-control')) }}
            </div>
        </div>


<div class="form-group">
    <label class="col-sm-2 control-label">&nbsp;</label>
    <div class="col-sm-10">
      {{ Form::submit(trans('fields.update'), array('class' => 'btn btn-lg btn-primary')) }}
      {{ link_to_route('campeonatoAdmins.index', trans('fields.cancel'), $campeonatoAdmin->id, array('class' => 'btn btn-lg btn-default')) }}
    </div>
</div>

{{ Form::close() }}
