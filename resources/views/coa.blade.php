@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    COA
@endsection

<!-- page title -->
@section('contentheader_title')
   Master COA
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
        <li class="active">Master COA</li>
    </ol>
@stop

@section('main-content')
	<div class="container spark-screen">
		<div class="row">
			<div class="col-md-11">
          		<!-- content -->

                <!-- template tabel -->
          		<table id="dg" title="Master COA" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
                    <!-- kolom -->
                    <thead>
                        <tr>
                            <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                            <th field="coa_year" width="50" sortable="true">COA Year</th>
                            <th field="coa_code" width="50" sortable="true">COA Code</th>
                            <th field="coa_name" width="50" sortable="true">COA Name</th>
                            <th field="coa_isparent" width="50" sortable="true">COA Parent</th>
                            <th field="coa_level" width="50" sortable="true">COA Level</th>
                            <th field="coa_type" width="50" sortable="true">COA Type</th>
                            <th field="coa_beginning" width="50" sortable="true">COA Beginning</th>
                            <th field="coa_debit" width="50" sortable="true">COA Debit</th>
                            <th field="coa_credit" width="50" sortable="true">COA Credit</th>
                            <th field="coa_ending" width="50" sortable="true">COA Ending</th>
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
                            <input name="coa_year" class="easyui-textbox" label="COA Year:" style="width:100%" data-options="required:true,validType:'length[0,4]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="coa_code" class="easyui-textbox" label="COA Code:" style="width:100%" data-options="required:true,validType:'length[0,5]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="coa_name" class="easyui-textbox" label="COA Name:" style="width:100%" data-options="required:true,validType:'length[0,100]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <select id="cc" class="easyui-combobox" name="coa_isparent" label="Active:" style="width:300px;">
                                <option value="true" selected>yes</option>
                                <option value="false">no</option>
                            </select>
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="coa_level" class="easyui-textbox" label="COA Level:" style="width:100%" data-options="required:true,numeric:true">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="coa_type" class="easyui-textbox" label="COA Type:" style="width:100%" data-options="required:true,validType:'length[0,10]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="coa_beginning" class="easyui-textbox" label="COA Beginning:" style="width:100%" data-options="required:true,validType:'length[0,10]'" value="0">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="coa_debit" class="easyui-textbox" label="COA Debit:" style="width:100%" data-options="required:true">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="coa_credit" class="easyui-textbox" label="COA Credit:" style="width:100%" data-options="required:true">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="coa_ending" class="easyui-textbox" label="COA Ending:" style="width:100%" data-options="required:true">
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
        var entity = "Master COA"; // nama si tabel, ditampilin di dialog
        var get_url = "{{route('coa.get')}}";
        var insert_url = "{{route('coa.insert')}}";
        var update_url = "{{route('coa.update')}}";
        var delete_url = "{{route('coa.delete')}}";

        $(function(){
            var dg = $('#dg').datagrid({
                url: get_url,
                pagination: true,
                remoteFilter: true, //utk jalanin search filter
                rownumbers: true,
                singleSelect: true,
                fitColumns: true,
                pageSize:30
            });
            dg.datagrid('enableFilter');
        });
</script>
<script src="{{asset('js/jeasycrud.js')}}"></script>
@endsection
