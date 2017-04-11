@extends('layouts.app')

@section('htmlheader_title')
	Home
@endsection

@section('contentheader_title')
   Welcome to JLM Accounting Apps
@endsection


@section('main-content')
	<div class="container spark-screen">
		<div class="row">
			<div class="col-md-10 col-md-offset-1">
				<center><img src="{{asset('img/logo.png')}}"></center>
			</div>
		</div>
	</div>
@endsection
