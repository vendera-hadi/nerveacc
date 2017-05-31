@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Invoice Type
@endsection

<!-- page title -->
@section('contentheader_title')
   Master Invoice Type
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
        <li class="active">Master Invoice Type</li>
    </ol>
@stop

@section('main-content')
<div class="row">
	<div class="col-md-12">
  		<!-- content -->

        <!-- template tabel -->
  		<table id="dg" title="Invoice Type" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
            <!-- kolom -->
            <thead>
                <tr>
                    <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                    <th field="invtp_name" width="50" sortable="true">Invoice Type Name</th>
                    <th field="invtp_prefix" width="50" sortable="true">Invoice Type Prefix</th>
                    <th field="invtp_coa_ar" width="50" sortable="true">Invoice Coa</th>
                </tr>
            </thead>
        </table>
        <!-- end table -->
        
        <!-- icon2 atas table -->
        <div id="toolbar">
            @if(Session::get('role')==1 || in_array(8,\Session::get('permissions')))
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newUser()">New</a>
            @endif
            @if(Session::get('role')==1 || in_array(9,\Session::get('permissions')))
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editUser()">Edit</a>
            @endif
            @if(Session::get('role')==1 || in_array(10,\Session::get('permissions')))
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="destroyUser()">Remove</a>
            @endif
        </div>
        <!-- end icon -->
    
        <!-- hidden form buat create edit -->
        <div id="dlg" class="easyui-dialog" style="width:60%"
                closed="true" buttons="#dlg-buttons">
            <form id="fm" method="post" novalidate style="margin:0;padding:20px 50px">
                <div style="margin-bottom:20px;font-size:14px;border-bottom:1px solid #ccc">Information</div>
                <div style="margin-bottom:10px">
                    <input name="invtp_name" class="easyui-textbox" required="true" label="Invoice Type Name:" style="width:100%">
                </div>
                <div style="margin-bottom:10px">
                    <input id="cc" class="easyui-combobox" required="true" name="invtp_coa_ar" style="width:100%" label="Coa Cost:" data-options="valueField:'id',textField:'text',url:'{{route('cost_item.getOptionsCoa')}}'">
                </div>
                <div style="margin-bottom:10px">
                    <input name="invtp_prefix" class="easyui-textbox" required="true" data-options="required:true,validType:'length[0,3]'" label="Prefix:" style="width:100%">
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
        var entity = "Invoice Type"; // nama si tabel, ditampilin di dialog
        var get_url = "{{route('invtype.get')}}";
        var insert_url = "{{route('invtype.insert')}}";
        var update_url = "{{route('invtype.update')}}";
        var delete_url = "{{route('invtype.delete')}}";

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
