@extends('ccc::layouts.master')

@section('content_left')

	{{ Form::open([\Route::getCurrentRoute()->getUri(), 'method' => 'post']) }}

		@DivOpen(12)
			<h5 style='color:{{ Session::get('message_color') }}' id='success_msg'>{{ Session::get('message') }}</h5>
		@DivClose()
		{{ Form::label('password', 'Password') }}
		{{ Form::password('password') }}
		{{ Form::label('password_confirm', trans('messages.password_confirm')) }}
		{{ Form::password('password_confirmation') }}

		<!-- errors -->
		@foreach ($errors->all() as $err)
			{{ $err }}
		@endforeach

		{{ Form::submit( \App\Http\Controllers\BaseViewController::translate_view('Save' , 'Button')) }}

	{{ Form::close() }}

@stop


@section('content')

	@include ('bootstrap.panel', array ('content' => 'content_left', 'view_header' => trans('messages.password_change'), 'md' => 4))

@stop