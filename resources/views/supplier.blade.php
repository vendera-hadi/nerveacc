@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Supplier
@endsection

<!-- page title -->
@section('contentheader_title')
   Master Supplier
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
        <li class="active">Master Supplier</li>
    </ol>
@stop

@section('main-content')
	<div class="container spark-screen">
		<div class="row">
			<div class="col-md-11">
          		<!-- content -->

                <!-- template tabel -->
          		<table id="dg" title="Master Supplier" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
                    <!-- kolom -->
                    <thead>
                        <tr>
                            <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                            <th field="coa_code" width="50" sortable="true">COA Code</th>
                            <th field="spl_code" width="50" sortable="true">Supplier Code</th>
                            <th field="spl_name" width="50" sortable="true">Supplier Name</th>
                            <th field="spl_address" width="50" sortable="true">Supplier Address</th>
                            <th field="spl_city" width="50" sortable="true">Supplier City</th>
                            <th field="spl_postal_code" width="50" sortable="true">Supplier Postal Code</th>
                            <th field="spl_phone" width="50" sortable="true">Supplier Phone</th>
                            <th field="spl_fax" width="50" sortable="true">Supplier Fax</th>
                            <th field="spl_cperson" width="50" sortable="true">Supplier Contact Person</th>
                            <th field="spl_npwp" width="50" sortable="true">Supplier NPWP</th>
                            <th field="spl_isactive" width="50" sortable="true">Supplier Active</th>
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
                            <select id="cc" class="easyui-combobox" required="true" name="coa_code" label="COA Code:" style="width:100%;">
                                @foreach($accounts as $coa)
                                <option value="{{$coa->coa_code}}" >{{$coa->coa_code." ".$coa->coa_name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="spl_code" class="easyui-textbox" label="Supplier Code:" style="width:100%" data-options="required:true,validType:'length[0,5]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="spl_name" class="easyui-textbox" label="Supplier Name:" style="width:100%" data-options="required:true,validType:'length[0,150]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="spl_address" class="easyui-textbox" label="Supplier Address:" style="width:100%" data-options="required:true,validType:'length[0,200]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="spl_city" class="easyui-textbox" label="Supplier City:" style="width:100%" data-options="required:true,validType:'length[0,255]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="spl_postal_code" class="easyui-textbox" label="Supplier Postal Code:" style="width:100%" data-options="required:true,validType:'length[0,5]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="spl_phone" class="easyui-textbox" label="Supplier Phone:" style="width:100%" data-options="validType:'length[0,20]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="spl_fax" class="easyui-textbox" label="Supplier Fax:" style="width:100%" data-options="validType:'length[0,20]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="spl_cperson" class="easyui-textbox" label="Supplier Contact Person:" style="width:100%" data-options="required:true,validType:'length[0,35]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="spl_npwp" class="easyui-textbox" label="Supplier NPWP:" style="width:100%" data-options="required:true,validType:'length[0,15]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <select id="cc" class="easyui-combobox" required="true" name="spl_isactive" label="Active:" style="width:300px;">
                                <option value="true" >yes</option>
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
	</div>
@endsection

@section('footer-scripts')
<script src="{{asset('plugins/jQueryUI/jquery-ui.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('plugins/jquery-easyui/jquery.easyui.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/datagrid-filter.js') }}"></script>
<script type="text/javascript">
        var entity = "Master Supplier"; // nama si tabel, ditampilin di dialog
        var get_url = "{{route('supplier.get')}}";
        var insert_url = "{{route('supplier.insert')}}";
        var update_url = "{{route('supplier.update')}}";
        var delete_url = "{{route('supplier.delete')}}";

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
