@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Tenant Type
@endsection

<!-- page title -->
@section('contentheader_title')
   Tenant / Owner Type
@endsection

<!-- tambahan script atas -->
@section('htmlheader_scripts')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/default/easyui.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/icon.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/color.css') }}">
    <style>
    .datagrid-wrap{
        height: 300px;
    }
    </style>
@endsection

@section('contentheader_breadcrumbs')
	<ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Tenant Type</li>
    </ol>
@stop

@section('main-content')
<div class="row">
	<div class="col-md-12">
  		<!-- content -->

        <!-- template tabel -->
  		<table id="dg" title="Master Tenant Type" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
            <!-- kolom -->
            <thead>
                <tr>
                    <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                    <th field="tent_name" width="50" sortable="true">Tenant Type</th>
                    <th field="tent_isowner" width="50" sortable="true">Is Owner</th>
                </tr>
            </thead>
        </table>
        <!-- end table -->
        
        <!-- icon2 atas table -->
        <div id="toolbar">
            @if(Session::get('role')==1 || in_array(32,Session::get('permissions')))
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newUser()">New</a>
            @endif
            @if(Session::get('role')==1 || in_array(33,Session::get('permissions')))
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editUser()">Edit</a>
            @endif
            @if(Session::get('role')==1 || in_array(34,Session::get('permissions')))
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="destroyUser()">Remove</a>
            @endif
        </div>
        <!-- end icon -->
    
        <!-- hidden form buat create edit -->
        <div id="dlg" class="easyui-dialog" style="width:60%"
                closed="true" buttons="#dlg-buttons">
            <form id="fm" method="post" novalidate style="margin:0;padding:20px 50px">
                <div style="margin-bottom:20px;font-size:14px;border-bottom:1px solid #ccc">Input Data</div>
                <div style="margin-bottom:10px">
                    <input name="tent_name" class="easyui-textbox" label="Tenant Name Type:" style="width:100%" data-options="required:true,validType:'length[0,15]'">
                </div>
                <div style="margin-bottom:10px">
                    <select id="cc" class="easyui-combobox" required="true" name="tent_isowner" label="Is Owner:" style="width:300px;">
                        <option value="true">yes</option>
                        <option value="false">no</option>
                    </select>
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
@endsection

@section('footer-scripts')
<script src="{{asset('plugins/jQueryUI/jquery-ui.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('plugins/jquery-easyui/jquery.easyui.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/datagrid-filter.js') }}"></script>
<script type="text/javascript">
        var entity = "Master Tenant Type"; // nama si tabel, ditampilin di dialog
        var get_url = "{{route('typetenant.get')}}";
        var insert_url = "{{route('typetenant.insert')}}";
        var update_url = "{{route('typetenant.update')}}";
        var delete_url = "{{route('typetenant.delete')}}";

        $(function(){
            var dg = $('#dg').datagrid({
                url: get_url,
                pagination: true,
                remoteFilter: true, //utk jalanin search filter
                rownumbers: true,
                singleSelect: true,
                fitColumns: true,
                pageSize:100,
                pageList: [100,500,1000],
            });
            dg.datagrid('enableFilter');
        });
</script>
<script src="{{asset('js/jeasycrud.js')}}"></script>
@endsection
