@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Fixed Assets
@endsection

<!-- page title -->
@section('contentheader_title')
   Master Fixed Assets
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
        <li class="active">Master Fixed Assets</li>
    </ol>
@stop

@section('main-content')
<div class="row">
	<div class="col-md-12">
  		<!-- content -->

        <!-- template tabel -->
  		<table id="dg" title="Master Fixed Assets" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
            <!-- kolom -->
            <thead>
                <tr>
                    <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                    <th field="fixas_code" width="120" sortable="true">Fixed Assets Code</th>
                    <th field="fixas_name" width="120" sortable="true">Fixed Assets Name</th>
                    <th field="fixas_aqc_date" width="120" sortable="true">Required Date</th>
                    <th field="fixas_age" width="120" sortable="true">Age</th>
                    <th field="fixas_supplier" width="120" sortable="true">Supplier</th>
                    <th field="fixas_pono" width="120" sortable="true">PO Number</th>
                    <th field="fixas_total_depr" width="120" sortable="true">Total</th>
                    <th field="fixas_isdelete" width="120" sortable="true">Deleted</th>
                    <th field="catas_name" width="120" sortable="true">Category Name</th>
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
                    <input name="fixas_code" class="easyui-textbox" required="true" data-options="required:true,validType:'length[0,15]'" label="Fixed Assets Code:" style="width:100%">
                </div>
                <div style="margin-bottom:10px">
                    <input name="fixas_name" class="easyui-textbox" required="true" data-options="required:true,validType:'length[0,50]'" label="Fixed Assets Name:" style="width:100%">
                </div>
                <div style="margin-bottom:10px">
                    <input id="dd" type="text" class="easyui-datebox" required="required" name="fixas_aqc_date" label="Acquired Date :" style="width:100%">
                </div>
                <div style="margin-bottom:10px">
                    <input name="fixas_age" class="easyui-textbox" required="true" data-options="required:true" label="Age:" style="width:100%">
                </div>
                <div style="margin-bottom:10px">
                    <input name="fixas_supplier" class="easyui-textbox" required="true" data-options="required:true,validType:'length[0,50]'" label="Supplier:" style="width:100%">
                </div>
                <div style="margin-bottom:10px">
                    <input name="fixas_pono" class="easyui-textbox" required="true" data-options="required:true,validType:'length[0,20]'" label="PO Number:" style="width:100%">
                </div>
                <div style="margin-bottom:10px">
                    <input name="fixas_total_depr" class="easyui-textbox" required="true" data-options="required:true" label="Total:" style="width:100%">
                </div>
                <div style="margin-bottom:10px">
                    <input id="cc" class="easyui-combobox" required="true" name="catas_id" style="width:100%" label="Category Assets:" data-options="valueField:'id',textField:'text',url:'{{route('fixed_asset.category_option')}}'">
                </div>
                <div style="margin-bottom:10px">
                    <select id="cc" class="easyui-combobox" required="true" name="fixas_isdelete" label="Deleted:" style="width:300px;">
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
        var entity = "Master Fixed Assets"; // nama si tabel, ditampilin di dialog
        var get_url = "{{route('fixed_asset.get')}}";
        var insert_url = "{{route('fixed_asset.insert')}}";
        var update_url = "{{route('fixed_asset.update')}}";
        var delete_url = "{{route('fixed_asset.delete')}}";

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
