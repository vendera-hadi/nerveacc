@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Cash Bank
@endsection

<!-- page title -->
@section('contentheader_title')
   Master Cash Bank
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
        <li class="active">Master Cash Bank</li>
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
                            <th field="cashbk_name" width="120" sortable="true">Kode Cash Bank</th>
                            <th field="cashbk_accn_no" width="120" sortable="true">Cash Bank Account No</th>
                            <th field="coa_code" width="120" sortable="true">Coa Code</th>
                            <th field="cashbk_isbank" width="120" sortable="true">Is Bank</th>
                            <th field="curr_name" width="120" sortable="true">Currency</th>
                        </tr>
                    </thead>
                </table>
                <!-- end table -->
                
                <!-- icon2 atas table -->
                <div id="toolbar">
                    @if(Session::get('role')==1 || in_array(73,Session::get('permissions')))
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newUser()">New</a>
                    @endif
                    @if(Session::get('role')==1 || in_array(74,Session::get('permissions')))
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editUser()">Edit</a>
                    @endif
                    @if(Session::get('role')==1 || in_array(75,Session::get('permissions')))
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
                            <input name="cashbk_name" class="easyui-textbox" label="Cash Bank Name:" style="width:100%" data-options="required:true,validType:'length[0,50]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="cashbk_accn_no" class="easyui-textbox" label="Account Number:" style="width:100%" data-options="required:true,validType:'length[0,15]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input id="cc" class="easyui-combobox" required="true" name="coa_code" style="width:100%" label="Coa Code:" data-options="valueField:'id',textField:'text',url:'{{route('cost_item.getOptionsCoa')}}'">
                        </div>
                        <div style="margin-bottom:10px">
                            <select id="cc" class="easyui-combobox" required="true" name="cashbk_isbank" label="Active:" style="width:300px;">
                                <option value="true">yes</option>
                                <option value="false">no</option>
                            </select>
                        </div>
                        <div style="margin-bottom:10px">
                            <input id="cc" class="easyui-combobox" required="true" name="curr_code" style="width:100%" label="Currency:" data-options="valueField:'id',textField:'text',url:'{{route('cash_bank.options')}}'">
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
        var entity = "Master Cash Bank"; // nama si tabel, ditampilin di dialog
        var get_url = "{{route('cash_bank.get')}}";
        var insert_url = "{{route('cash_bank.insert')}}";
        var update_url = "{{route('cash_bank.update')}}";
        var delete_url = "{{route('cash_bank.delete')}}";

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
