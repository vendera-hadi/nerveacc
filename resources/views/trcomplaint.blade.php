@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Unit Complaint
@endsection

<!-- page title -->
@section('contentheader_title')
   Unit Complaint
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
        <li class="active">Unit Complaint</li>
    </ol>
@stop

@section('main-content')
    <div class="container spark-screen">
        <div class="row">
            <div class="col-md-11">
                <!-- content -->

                <!-- template tabel -->
                <table id="dg" title="Unit Complaint" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
                    <!-- kolom -->
                    <thead>
                        <tr>
                            <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                            <th field="comtr_no" width="50" sortable="true">Complaint No</th>
                            <th field="comtr_date" width="50" sortable="true">Complaint Date</th>
                            <th field="comtr_note" width="50" sortable="true">Complaint Note</th>
                            <th field="comtr_handling_date" width="50" sortable="true">Handling Date</th>
                            <th field="comtr_handling_by" width="50" sortable="true">Handled By</th>
                            <th field="comtr_finish_date" width="50" sortable="true">Finish Date</th>
                            <th field="comtr_handling_note" width="50" sortable="true">Handling Note</th>
                            <th field="unit_name" width="50" sortable="true">Unit Name</th>
                            <th field="compl_code" width="50" sortable="true">Complaint Code</th>
                            <th field="created_by" width="50" >Created By</th>
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
                            <input name="comtr_date" type="text" class="easyui-datebox" required="true" label="Unit Complaint Date:" style="width:100%">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="comtr_note" type="text" class="easyui-textbox" required="true" label="Note:" style="width:100%">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="comtr_handling_date" type="text" class="easyui-datebox" label="Handling Date:" style="width:100%">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="comtr_handling_by" type="text" class="easyui-textbox" label="Handled By:" style="width:100%">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="comtr_finish_date" type="text" class="easyui-datebox" label="Finish Date:" style="width:100%">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="comtr_handling_note" type="text" class="easyui-textbox"  label="Handling Note:" style="width:100%">
                        </div>
                        <div style="margin-bottom:10px">
                            <input id="cc" class="easyui-combobox" required="true" name="unit_id" style="width:100%" label="Unit :" data-options="valueField:'id',textField:'text',url:'{{route('unit.all')}}'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input id="cc" class="easyui-combobox" required="true" name="compl_id" style="width:100%" label="Complaint Code:" data-options="valueField:'id',textField:'text',url:'{{route('complaint.options')}}'">
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
        var entity = "Unit Complaint"; // nama si tabel, ditampilin di dialog
        var get_url = "{{route('unitcomplaint.get')}}";
        var insert_url = "{{route('unitcomplaint.insert')}}";
        var update_url = "{{route('unitcomplaint.update')}}";
        var delete_url = "{{route('unitcomplaint.delete')}}";

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
