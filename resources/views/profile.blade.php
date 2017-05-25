@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Company
@endsection

<!-- page title -->
@section('contentheader_title')
  	Company Detail
@endsection

<!-- tambahan script atas -->
@section('htmlheader_scripts')
    
@endsection

@section('contentheader_breadcrumbs')
	<ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Profile</li>
    </ol>
@stop

@section('main-content')
<div class="row">
	<div class="col-md-12">
		<div class="box box-primary">
		    <div class="box-header with-border">
		      <h3 class="box-title">Profile Details</h3>
		    </div> 
		    <form action="{{route('profile.update')}}" method="post" enctype="multipart/form-data">
		      <div class="box-body">
		      		@if(Session::get('error'))
				    	<div class="alert alert-danger">
						  <strong>Error!</strong> {{ Session::get('error') }}
						</div>
				    @endif

				    @if(Session::get('success'))
				    	<div class="alert alert-success">
						  <strong>Success</strong> {{ Session::get('success') }}
						</div>
				    @endif

				    @if (count($errors) > 0)
				      <div class="alert alert-danger">
				        <ul>
				          @foreach($errors->all() as $error)
				            <li>{{ $error }}</li>
				          @endforeach
				        </ul>
				      </div>
				    @endif	    
		        <div class="col-sm-12">
					<div class="form-group">
		              <label>Name</label>
		              <input type="text" value="{{$users->name}}" name="name" class="form-control" id="Name" placeholder="Input Name" required>
		            </div>

		            <div class="form-group">
		              <label>Email</label>
		              <input type="email" value="{{$users->email}}" name="email" class="form-control" id="Email" placeholder="Input Email" required>
		            </div>

		            <div class="form-group">
		              <label>Password</label>
		              <input type="password" name="password" class="form-control" id="Password" placeholder="Your Password" value="xxx">
		            </div>

		            @if($users->image)
					<div class="form-group">
		              <label>Current Image</label>
		              <img src="{{asset('upload/'.$users->image)}}" class="img-responsive" style="max-width: 300px;">
		            </div>						                
		            @endif

		            <div class="form-group">
		              <label>Image</label>
		              <input type="file" name="image" id="usersImage">
		              <p class="help-block">masukkan gambar anda</p>
		            </div>
		        
				</div>
		      </div>
		      <!-- /.box-body -->

		      <div class="box-footer">
		        <button type="submit" class="btn btn-primary">Submit</button>
		      </div>
		    </form>
	  	</div>
	</div>
</div>
@endsection
