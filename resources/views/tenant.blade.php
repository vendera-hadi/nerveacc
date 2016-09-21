@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Tenant
@endsection

<!-- page title -->
@section('contentheader_title')
   Master Tenant
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
        <li class="active">Master Tenant</li>
    </ol>
@stop

@section('main-content')
	<div class="container spark-screen">
		<div class="row">
			<div class="col-md-11">
          		<!-- content -->

                <!-- template tabel -->
          		<table id="dg" title="Master Tenant" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
                    <!-- kolom -->
                    <thead>
                        <tr>
                            <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                            <th field="tenan_id" width="50" sortable="true">ID</th>
                            <th field="tenan_code" width="50" sortable="true">Tenant Code</th>
                            <th field="tenan_name" width="50" sortable="true">Tenant Name</th>
                            <th field="tenan_idno" width="50" sortable="true">No Unit</th>
                            <th field="tenan_phone" width="50" sortable="true">Phone</th>
                            <th field="tenan_address" width="50" sortable="true">Address</th>
                            <th field="tenan_npwp" width="50" sortable="true">NPWP</th>
                            <th field="tenan_taxname" width="50" sortable="true">Tax Name</th>
                            <th field="tenan_tax_address" width="50" sortable="true">Tenan Tax Address</th>
                            <th field="tent_name" width="50" sortable="true">Tenant Type</th>
                        </tr>
                    </thead>
                </table>
                <!-- end table -->
                
                <!-- icon2 atas table -->
                <div id="toolbar">
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newUser()">New</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editUser()">Edit</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="destroyUser()">Remove</a>
                </div>
                <!-- end icon -->
            
                <!-- hidden form buat create edit -->
                <div id="dlg" class="easyui-dialog" style="width:60%"
                        closed="true" buttons="#dlg-buttons">
                    <form id="fm" method="post" novalidate style="margin:0;padding:20px 50px">
                        <div style="margin-bottom:20px;font-size:14px;border-bottom:1px solid #ccc">Input Data</div>
                        <div style="margin-bottom:10px">
                            <input name="tenan_code" class="easyui-textbox" label="Unit Name:" style="width:100%" data-options="required:true,validType:'length[0,15]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="tenan_name" class="easyui-textbox" label="Tenant Name:" style="width:100%" data-options="required:true,validType:'length[0,80]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="tenan_idno" class="easyui-textbox" label="Unit No:" style="width:100%" data-options="required:true,validType:'length[0,20]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="tenan_phone" class="easyui-textbox" label="Tenant Phone:" style="width:100%" data-options="required:true,validType:'length[0,15]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="tenan_email" class="easyui-textbox" label="Tenant Email:" style="width:100%" data-options="required:true,validType:'length[0,80]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="tenan_address" class="easyui-textbox" label="Tenant Address:" style="width:100%" data-options="required:true,validType:'length[0,150]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="tenan_npwp" class="easyui-textbox" label="Tenant NPWP:" style="width:100%" data-options="validType:'length[0,15]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="tenan_taxname" class="easyui-textbox" label="Tenant Taxname:" style="width:100%" data-options="validType:'length[0,50]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="tenan_tax_address" class="easyui-textbox" label="Tenant Tax Address:" style="width:100%" data-options="validType:'length[0,150]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input id="cc" class="easyui-combobox" required="true" name="tent_id" style="width:100%" label="Tenant Type:" data-options="valueField:'id',textField:'text',url:'{{route('tenant.options')}}'">
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
<script type="text/javascript" src="{{ asset('js/datagrid-filter.js') }}"></script>
<script type="text/javascript">
        var entity = "Master Tenant"; // nama si tabel, ditampilin di dialog
        var get_url = "{{route('tenant.get')}}";
        var insert_url = "{{route('tenant.insert')}}";
        var update_url = "{{route('tenant.update')}}";
        var delete_url = "{{route('tenant.delete')}}";

        $(function(){
            var dg = $('#dg').datagrid({
                url: get_url,
                pagination: true,
                remoteFilter: true, //utk jalanin search filter
                rownumbers: true,
                singleSelect: true,
                fitColumns: true
            });
            dg.datagrid('enableFilter');
        });
</script>
<script src="{{asset('js/jeasycrud.js')}}"></script>
@endsection
