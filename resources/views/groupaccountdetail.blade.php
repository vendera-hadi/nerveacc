@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Group Account Detail
@endsection

<!-- page title -->
@section('contentheader_title')
   Master Group Account Detail
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
        <li class="active">Master Group Account Detail</li>
    </ol>
@stop

@section('main-content')
	<div class="container spark-screen">
		<div class="row">
			<div class="col-md-11">
          		<!-- content -->

                <!-- template tabel -->
          		<table id="dg" title="Group Account Detail" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
                    <!-- kolom -->
                    <thead>
                        <tr>
                            <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                            <th field="id" width="50" sortable="true">ID</th>
                            <th field="grpaccn_name" width="50" sortable="true">Group Acc Detail Name</th>
                            <th field="coa_year" width="50" sortable="true">COA year</th>
                            <th field="coa_code" width="50" sortable="true">COA code</th>
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
                        <div style="margin-bottom:20px;font-size:14px;border-bottom:1px solid #ccc">Information</div>
                        <div style="margin-bottom:10px">
                            <input id="cc" class="easyui-combobox" required="true" name="grpaccn_id" style="width:100%" label="Group Account:" data-options="valueField:'id',textField:'text',url:'{{route('groupaccount.options')}}'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input id="cc" class="easyui-combobox" required="true" name="coa_year" style="width:100%" label="COA Year:" data-options="valueField:'id',textField:'text',url:'{{route('coa.year')}}'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input id="cc" class="easyui-combobox" required="true" name="coa_code" style="width:100%" label="COA Code:" data-options="valueField:'id',textField:'text',url:'{{route('coa.code')}}'">
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
        var entity = "User"; // nama si tabel, ditampilin di dialog
        var get_url = "{{route('groupaccdetail.get')}}";
        var insert_url = "{{route('groupaccdetail.insert')}}";
        var update_url = "{{route('groupaccdetail.update')}}";
        var delete_url = "{{route('groupaccdetail.delete')}}";

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
