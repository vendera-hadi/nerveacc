@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Company
@endsection

<!-- page title -->
@section('contentheader_title')
   Master Company
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
        <li class="active">Master Company</li>
    </ol>
@stop

@section('main-content')
	<div class="container spark-screen">
		<div class="row">
			<div class="col-md-11">
          		<!-- content -->

                <!-- template tabel -->
          		<table id="dg" title="Master Unit" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
                    <!-- kolom -->
                    <thead>
                        <tr>
                            <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                            <th field="id" width="120" sortable="true">ID</th>
                            <th field="comp_name" width="120" sortable="true">Company Name</th>
                            <th field="comp_address" width="120" sortable="true">Address</th>
                            <th field="comp_phone" width="120" sortable="true">Phone</th>
                            <th field="comp_fax" width="120" sortable="true">Fax</th>
                            <th field="comp_sign_inv_name" width="120" sortable="true">Sign Invoice Name</th>
                            <th field="comp_build_insurance" width="120" sortable="true">Build Insurance</th>
                            <th field="comp_npp_insurance" width="120" sortable="true">NPP Insurance</th>
                            <th field="cashbk_code" width="120" sortable="true">Cash Bank Name</th>
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
                            <input name="comp_name" class="easyui-textbox" label="Company Name:" style="width:100%" data-options="required:true,validType:'length[0,100]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="comp_address" class="easyui-textbox" label="Address:" style="width:100%" data-options="required:true,validType:'length[0,150]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="comp_phone" class="easyui-textbox" label="Phone:" style="width:100%" data-options="required:true,validType:'length[0,20]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="comp_fax" class="easyui-textbox" label="Fax:" style="width:100%" data-options="required:true,validType:'length[0,20]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="comp_sign_inv_name" class="easyui-textbox" label="Sign Name:" style="width:100%" data-options="required:true,validType:'length[0,40]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="comp_build_insurance" class="easyui-textbox" label="Build Insurance:" style="width:100%" data-options="required:true,validType:'length[0,20]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="comp_npp_insurance" class="easyui-textbox" label="NPP Insurance:" style="width:100%" data-options="required:true,validType:'length[0,14]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input id="cc" class="easyui-combobox" required="true" name="cashbk_id" style="width:100%" label="Currency:" data-options="valueField:'id',textField:'text',url:'{{route('company.options')}}'">
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
        var entity = "Master Company"; // nama si tabel, ditampilin di dialog
        var get_url = "{{route('company.get')}}";
        var insert_url = "{{route('company.insert')}}";
        var update_url = "{{route('company.update')}}";
        var delete_url = "{{route('company.delete')}}";

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
