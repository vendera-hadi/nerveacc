@extends('layouts.app')

@section('contentheader_title')
   Contoh Page Header
@endsection

@section('htmlheader_title')
	Contoh Title
@endsection

@section('htmlheader_scripts')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/default/easyui.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/icon.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/color.css') }}">
@endsection

@section('contentheader_breadcrumbs')
	<ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Contoh</li>
    </ol>
@stop

@section('main-content')
	<div class="container spark-screen">
		<div class="row">
			<div class="col-md-11">
          		<!-- content -->

                <!-- template tabel -->
          		<table id="dg" title="My Users" class="easyui-datagrid" style="width:100%;height:100%"
                        url="{{route('contoh.get')}}"
                        toolbar="#toolbar" pagination="true"
                        rownumbers="true" fitColumns="true" singleSelect="true">
                    <!-- kolom -->
                    <thead>
                        <tr>
                            <th field="firstname" width="50">First Name</th>
                            <th field="lastname" width="50">Last Name</th>
                            <th field="phone" width="50">Phone</th>
                            <th field="email" width="50">Email</th>
                        </tr>
                    </thead>
                </table>
                <!-- end table -->
                
                <!-- icon2 atas table -->
                <div id="toolbar">
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newUser()">New User</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editUser()">Edit User</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="destroyUser()">Remove User</a>
                </div>
                <!-- end icon -->
            
                <!-- hidden form buat create edit -->
                <div id="dlg" class="easyui-dialog" style="width:400px"
                        closed="true" buttons="#dlg-buttons">
                    <form id="fm" method="post" novalidate style="margin:0;padding:20px 50px">
                        <div style="margin-bottom:20px;font-size:14px;border-bottom:1px solid #ccc">User Information</div>
                        <div style="margin-bottom:10px">
                            <input name="firstname" class="easyui-textbox" required="true" label="First Name:" style="width:100%">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="lastname" class="easyui-textbox" required="true" label="Last Name:" style="width:100%">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="phone" class="easyui-textbox" required="true" label="Phone:" style="width:100%">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="email" class="easyui-textbox" required="true" validType="email" label="Email:" style="width:100%">
                        </div>
                    </form>
                </div>
                <div id="dlg-buttons">
                    <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveUser()" style="width:90px">Save</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg').dialog('close')" style="width:90px">Cancel</a>
                </div>
                <!-- end form -->

          		<!-- content -->
        	</div>
		</div>
	</div>
@endsection

@section('footer-scripts')
<script src="{{asset('plugins/jQueryUI/jquery-ui.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('plugins/jquery-easyui/jquery.easyui.min.js') }}"></script>
<script type="text/javascript">
        var entity = "User"; // nama si tabel, ditampilin di dialog
        var insert_url = "{{route('contoh.insert')}}";
        var update_url = "{{route('contoh.update')}}";
        var delete_url = "{{route('contoh.delete')}}";
</script>
<script src="{{asset('js/jeasycrud.js')}}"></script>
@endsection
