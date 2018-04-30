@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Budget
@endsection

<!-- page title -->
@section('contentheader_title')
    Budget
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
        <li class="active">Budget</li>
    </ol>
@stop

@section('main-content')
<div class="row">
    <div class="col-md-12">
        @if(Session::has('msg'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h4><i class="icon fa fa-check"></i> Alert!</h4>
                Success Upload Excel
            </div>
        @endif

        @if(Session::has('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                {{Session::get('error')}}
            </div>
        @endif
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#tab_1" data-toggle="tab">Lists</a></li>
              <li><a href="#tab_2" data-toggle="tab">Budget Detail</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab_1">
                    <table id="dg" title="Budget Tahunan" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
                        <thead>
                            <tr>
                                <th field="tahun" width="50" sortable="true">ID</th>
                                <th field="created_by" width="50" >Created By</th>
                                <th field="created_at" width="50" >Created At</th>
                            </tr>
                        </thead>
                    </table>
                    <div id="toolbar">
                        @if(Session::get('role')==1 || in_array(77,Session::get('permissions')))
                        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newUser()">New</a>
                        @endif
                        @if(Session::get('role')==1 || in_array(77,Session::get('permissions')))
                        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editUser()">Edit</a>
                        @endif
                        @if(Session::get('role')==1 || in_array(77,Session::get('permissions')))
                        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="destroyUser()">Remove</a>
                        @endif
                        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-search" plain="true" onclick="detail()">View</a>
                    </div>
                    <div id="dlg" class="easyui-dialog" style="width:60%" closed="true" buttons="#dlg-buttons">
                        <form id="fm" method="post" novalidate style="margin:0;padding:20px 50px">
                            <div style="margin-bottom:20px;font-size:14px;border-bottom:1px solid #ccc">Information</div>
                            <div style="margin-bottom:10px">
                                    <input name="tahun" class="easyui-textbox" label="Tahun:" style="width:100%" data-options="required:true,validType:'length[0,4]'">
                            </div>
                        </form>
                    </div>
                    <div id="dlg-buttons">
                        <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveUser()" style="width:90px">Save</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg').dialog('close')" style="width:90px">Cancel</a>
                    </div>
                </div>
                <div class="tab-pane" id="tab_2">
                    <div id="editData" style="max-height: 100%; width: 100%; overflow: hidden;">
                        <div class="data-body"></div>
                    </div>
                </div>
            </div>
        </div>

@endsection

@section('footer-scripts')
<script src="{{asset('plugins/jQueryUI/jquery-ui.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('plugins/jquery-easyui/jquery.easyui.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/datagrid-filter.js') }}"></script>
<script src="{{asset('js/jeasycrud.js')}}"></script>
<script type="text/javascript">
        var entity = "Budget";
        var get_url = "{{route('budget.get')}}";
        var insert_url = "{{route('budget.insert')}}";
        var update_url = "{{route('budget.update')}}";
        var delete_url = "{{route('budget.delete')}}";

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

function detail(){
    var row = $('#dg').datagrid('getSelected');
    if (row){
        id = row.id;
        $.post('{{route('budget.detail')}}',{id:id},function(result){
            if
                (result.errorMsg) $.messager.alert('Warning',result.errorMsg);
            else
                $('#editData').find('.data-body').html('');
                $('#editData').find('.data-body').html(result);
        });
        $('.nav-tabs a[href="#tab_2"]').tab('show');

    }
}

$(document).delegate('.numeric','keypress', function(event) {
    var $this = $(this);
    if ((event.which != 46 || $this.val().indexOf('.') != -1) &&
       ((event.which < 48 || event.which > 57) &&
       (event.which != 0 && event.which != 8))) {
           event.preventDefault();
    }

    var text = $(this).val();
    if ((event.which == 46) && (text.indexOf('.') == -1)) {
        setTimeout(function() {
            if ($this.val().substring($this.val().indexOf('.')).length > 3) {
                $this.val($this.val().substring(0, $this.val().indexOf('.') + 3));
            }
        }, 1);
    }

    if ((text.indexOf('.') != -1) &&
        (text.substring(text.indexOf('.')).length > 2) &&
        (event.which != 0 && event.which != 8) &&
        ($(this)[0].selectionStart >= text.length - 2)) {
            event.preventDefault();
    }
});

</script>
@endsection
