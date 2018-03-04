@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    ACL Users
@endsection

<!-- page title -->
@section('contentheader_title')
   ACL Users
@endsection

<!-- tambahan script atas -->
@section('htmlheader_scripts')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/default/easyui.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/icon.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/color.css') }}">
@endsection

@section('contentheader_breadcrumbs')
	<ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">ACL Users</li>
    </ol>
@stop

@section('main-content')
    @if(Session::has('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-check"></i> {{Session::get('success')}}</h4>
      </div>
    @endif

<div class="row">
	<div class="col-md-12">
      		<!-- content -->
      		<div class="row">
                <div class="col-md-6">
                    <div class="box">
                        <div class="box-header with-border">
                          <h3 class="box-title">Tabel user</h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <button type="button" class="btn btn-flat btn-primary pull-right" style="margin-bottom:10px" id="AddNew">Add new User</button>
                          <table class="table table-bordered">
                            <tbody>
                                <tr>
                                  <th>Nama User</th>
                                  <th>Role</th>
                                  <th style="width: 40px">Action</th>
                                </tr>
                                @foreach($users as $user)
                                <tr>
                                  <td>{{ $user->name }}</td>
                                  <td>{{ $user->role }}</td>
                                  <td data-id="{{$user->id}}">
                                    @if($user->role_id!=1 || $first_superadmin != $user->id)
                                    <a href="javascript:void(0);" class="edit"><i class="fa fa-pencil"></i></a>
                                    <a href="javascript:void(0);" class="delete"><i class="fa fa-times"></i></a>
                                    @endif
                                  </td>
                                </tr>
                                @endforeach
                            </tbody>
                           </table>
                        </div>
                        <!-- /.box-body -->
                        <div class="box-footer clearfix">
                            {{ $users->render() }}
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                	<!-- box insert -->
                    <div class="box" id="addBox" style="display:none">
                        <div class="box-header with-border">
                          <h3 class="box-title editor">Add new User</h3>
                        </div>
                            <form id="formInsert" role="form" action="{{route('users.insert')}}" method="post">
                              <div class="box-body">
                                <div class="form-group">
                                  <label for="exampleInputEmail1">Name</label>
                                  <input type="text" class="form-control" name="name" placeholder="Name" required>
                                </div>

                                <div class="form-group">
                                  <label for="exampleInputEmail1">Email</label>
                                  <input type="email" class="form-control" name="email" placeholder="Email" required>
                                </div>

                                <div class="form-group">
                                  <label for="exampleInputEmail1">Password</label>
                                  <input type="password" class="form-control" name="password" placeholder="Password" required>
                                </div>

                                <div class="form-group">
                                  <label for="exampleInputEmail1">Role</label>
                                  <select class="form-control" name="role_id">
                                  	@foreach($roles as $role)
                                  	<option value="{{$role->id}}">{{$role->name}}</option>
                                  	@endforeach
                                  </select>
                                </div>

                                <button type="submit" class="btn btn-primary" style="margin-top:25px">Submit</button>

                            </form>
                        </div>
                    </div>
                    <!-- box insert -->

                    <!-- box update -->
                    <div class="box" id="editBox" style="display:none">
                        <div class="box-header with-border">
                          <h3 class="box-title editor">Edit User</h3>
                        </div>
                            <form id="formUpdate" role="form" action="{{route('users.update')}}" method="post">
                              <input type="hidden" name="user_id">
                              <div class="box-body">
                                <div class="form-group">
                                  <label for="exampleInputEmail1">Name</label>
                                  <input type="text" class="form-control" name="name" placeholder="Name" required>
                                </div>

                                <div class="form-group">
                                  <label for="exampleInputEmail1">Email</label>
                                  <input type="email" class="form-control" name="email" placeholder="Email" required>
                                </div>

                                <div class="form-group">
                                  <label for="exampleInputEmail1">Password (isi kalau ingin diubah)</label>
                                  <input type="password" class="form-control" name="password" placeholder="Password">
                                </div>

                                <div class="form-group">
                                  <label for="exampleInputEmail1">Role</label>
                                  <select class="form-control" name="role_id">
                                  	@foreach($roles as $role)
                                  	<option value="{{$role->id}}">{{$role->name}}</option>
                                  	@endforeach
                                  </select>
                                </div>

                                <button type="submit" class="btn btn-primary" style="margin-top:25px">Submit</button>

                            </form>
                        </div>
                    </div>
                    <!-- box update -->
                </div>


            </div>
      		<!-- content -->
    	</div>
</div>
@endsection

@section('footer-scripts')
<script type="text/javascript">
	$('.delete').click(function(){
        var id = $(this).parent().data('id');
        if(confirm('Are you sure want to delete this user ?')){
            $.post('{{route('users.delete')}}',{id: id}, function(result){
                if(result.success) location.reload();
                if(result.errorMsg) alert(result.errorMsg);
            });
        }
    });

    $('#AddNew').click(function(){
    	$('#editBox').hide();
    	$('#addBox').show();
    	$('#formInsert').trigger("reset");
    });

    $('#formInsert').submit(function(e){
    	e.preventDefault();
    	$.post('{{route('users.insert')}}',$(this).serialize(), function(result){
                if(result.success) location.reload();
                if(result.errorMsg) alert(result.errorMsg);
            });
    });

    $('#formUpdate').submit(function(e){
    	e.preventDefault();
    	$.post('{{route('users.update')}}',$(this).serialize(), function(result){
                if(result.success) location.reload();
                if(result.errorMsg) alert(result.errorMsg);
            });
    });

    $('.edit').click(function(){
        $('#editBox').show();
    	$('#addBox').hide();
        var id = $(this).parent().data('id');
        $('#formUpdate').find('input[name=user_id]').val(id);
        $.post('{{route('users.detail')}}',{id: id}, function(result){
            if(result.success){
                console.log(result.data);
                $('#formUpdate').find('input[name=name]').val(result.data.name);
                $('#formUpdate').find('input[name=email]').val(result.data.email);
                $('#formUpdate').find('select[name=role_id]').val(result.data.role_id);
            }
            if(result.errorMsg) alert(result.errorMsg);
        });
    });
</script>
@endsection