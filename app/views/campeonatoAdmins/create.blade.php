<div class="row">
    <div class="col-md-10 col-md-offset-2">
        <h1>{{ trans('messages.campeonatoAdmin_create') }}</h1>

        @if ($errors->any())
        	<div class="alert alert-danger">
        	    <ul>
                    {{ implode('', $errors->all('<li class="error">:message</li>')) }}
                </ul>
        	</div>
        @endif
    </div>
</div>

{{ Form::open(array('route' => 'campeonatoAdmins.store', 'class' => 'form-horizontal')) }}

        <div class="form-group">
            {{ Form::label('users_id', trans('fields.users_id'), array('class'=>'col-md-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::input('number', 'users_id', Input::old('users_id'), array('class'=>'form-control')) }}
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-10">
              {{ Form::input('hidden', 'campeonatos_id', $campeonatos_id , array('class'=>'form-control')) }}
            </div>
        </div>


<div class="form-group">
    <label class="col-sm-2 control-label">&nbsp;</label>
    <div class="col-sm-10">
      {{ Form::submit(trans('fields.create'), array('class' => 'btn btn-primary', 'id'=>'botao_confirmar')) }}
      {{ link_to_route('campeonatoAdmins.index', trans('fields.cancel'), '', array('class' => 'btn btn-default', 'id'=>'botao_cancelar')) }}
    </div>
</div>

{{ Form::close() }}
