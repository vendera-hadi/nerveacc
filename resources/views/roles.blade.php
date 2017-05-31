@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    User Roles
@endsection

<!-- page title -->
@section('contentheader_title')
   User Roles
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
        <li class="active">User Roles</li>
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
                      <h3 class="box-title">Tabel grup pengguna</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <button type="button" class="btn btn-primary pull-right" style="margin-bottom:10px" id="AddNew">Add new Role</button>
                      <table class="table table-bordered">
                        <tbody>
                            <tr>
                              <th>Nama Grup</th>
                              <th style="width: 40px">Action</th>
                            </tr>
                            @foreach($roles as $role)
                            <tr>
                              <td>{{ $role->name }}</td>
                              <td data-id="{{$role->id}}">
                                @if($role->id!=1)
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
                        {{ $roles->render() }} 
                    </div>
                </div>
            </div>
        
            <div class="col-md-6">
                <div class="box" id="editorBox" style="display:none">
                    <div class="box-header with-border">
                      <h3 class="box-title editor"></h3>
                    </div>
                        <form id="formRole" role="form" action="" method="post">
                          <div class="box-body">
                            <div class="form-group">
                              <label for="exampleInputEmail1">Role Name</label>
                              <input type="text" class="form-control" name="name" placeholder="Role Name" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="checkbox">
                                      <label>
                                        <input type="checkbox" id="selectAll"> Select / Unselect All
                                      </label>
                                    </div>
                                </div>
                                @foreach($permissions as $permission)
                                <div class="col-sm-6">
                                    <div class="checkbox">
                                      <label>
                                        <input type="checkbox" class="permission" name="permission[]" value="{{$permission->id}}"> {{$permission->name}}
                                      </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                          
                            <button type="submit" class="btn btn-primary" style="margin-top:25px">Submit</button>
                          
                        </form>
                    </div>
                </div>
            </div>
        </div>

  		<!-- content -->
	</div>
</div>
@endsection

@section('footer-scripts')
<script type="text/javascript">
    $('#AddNew').click(function(){
        $('#editorBox').show();
        $('.editor').text('Add New Role');
        $('input[name=name]').val('');
        $('.permission').prop('checked',false);
        $('#formRole').attr('action','{{route('roles.insert')}}');
    });

    $('#selectAll').change(function(){
        if($(this).is(':checked')){
            $('.permission').prop('checked',true);
        }else{
            $('.permission').prop('checked',false);
        }
    });

    $('.delete').click(function(){
        var id = $(this).parent().data('id');
        if(confirm('Are you sure want to delete this role ?')){
            $.post('{{route('roles.delete')}}',{id: id}, function(result){
                if(result.success) location.reload();
                if(result.errorMsg) alert(result.errorMsg);
            });
        }
    });

    $('.edit').click(function(){
        $('#editorBox').show();
        $('.editor').text('Edit Role');
        $('.permission').prop('checked',false);
        var id = $(this).parent().data('id');
        $('#formRole').attr('action','{{url('roles/update')}}/'+id);
        $.post('{{route('roles.detail')}}',{id: id}, function(result){
            if(result.success){
                console.log(result.data);
                $('input[name=name]').val(result.data.name);
                $('.permission').each(function(){
                    if($.inArray( parseInt($(this).val()), result.data.permissions) != -1){
                        $(this).prop('checked',true);
                    }
                });
            }
            if(result.errorMsg) alert(result.errorMsg);
        });
    });        
</script>
@endsection