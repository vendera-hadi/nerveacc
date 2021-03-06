@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Period Meter
@endsection

<!-- page title -->
@section('contentheader_title')
Period Meter
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
        <li class="active">Period Meter</li>
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
                <!-- content -->
                <!-- Tabs -->
                <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                  <li class="active"><a href="#tab_1" data-toggle="tab">Lists</a></li>
                  <li><a href="#tab_2" data-toggle="tab">Meter</a></li>
                </ul>
                <div class="tab-content">
                  <div class="tab-pane active" id="tab_1">

                <!-- template tabel -->
                <table id="dg" title="Period Meter" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
                    <!-- kolom -->
                    <thead>
                        <tr>
                            <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                            <th field="prdmet_id" width="50" sortable="true">ID</th>
                            <th field="prdmet_start_date" width="50" sortable="true">Start Date</th>
                            <th field="prdmet_end_date" width="50" sortable="true">End Date</th>
                            <th field="prd_billing_date" width="50" sortable="true">Period Billing</th>
                            <th field="created_by" width="50" >Created By</th>
                            <th field="status" width="50" sortable="true">Status</th>
                        </tr>
                    </thead>
                </table>
                <!-- end table -->

                <!-- icon2 atas table -->
                <div id="toolbar">
                    @if(Session::get('role')==1 || in_array(53,Session::get('permissions')))
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newUser()">New</a>
                    @endif
                    @if(Session::get('role')==1 || in_array(54,Session::get('permissions')))
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editUser()">Edit</a>
                    @endif
                    @if(Session::get('role')==1 || in_array(55,Session::get('permissions')))
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="destroyUser()">Remove</a>
                    @endif
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-search" plain="true" onclick="detail()">View</a>
                    @if(Session::get('role')==1 || in_array(56,Session::get('permissions')))
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" plain="true" onclick="approve()">Approve</a>
                    @endif
                    @if(Session::get('role')==1 || in_array(57,Session::get('permissions')))
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="unposting()">UnPosting</a>
                    @endif
                </div>
                <!-- end icon -->

                <!-- hidden form buat create edit -->
                <div id="dlg" class="easyui-dialog" style="width:60%"
                        closed="true" buttons="#dlg-buttons">
                    <form id="fm" method="post" novalidate style="margin:0;padding:20px 50px">
                        <div style="margin-bottom:20px;font-size:14px;border-bottom:1px solid #ccc">Information</div>
                        <div style="margin-bottom:10px">
                            <input type="text" class="easyui-datebox" required="required" name="prdmet_start_date" label="Start Date :" style="width:100%" data-options="formatter:myformatter,parser:myparser">
                        </div>
                        <div style="margin-bottom:10px">
                            <input type="text" class="easyui-datebox" required="required" name="prdmet_end_date" label="End Date :" style="width:100%" data-options="formatter:myformatter,parser:myparser">
                        </div>
                        <div style="margin-bottom:10px">
                            <input type="text" class="easyui-datebox" required="required" name="prd_billing_date" label="Periode Billing :" style="width:100%" data-options="formatter:myformatter,parser:myparser">
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
                <!-- /.tab-pane -->
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
        var entity = "Period Meter"; // nama si tabel, ditampilin di dialog
        var get_url = "{{route('period_meter.get')}}";
        var insert_url = "{{route('period_meter.insert')}}";
        var update_url = "{{route('period_meter.update')}}";
        var delete_url = "{{route('period_meter.delete')}}";

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
        status = row.status;
        $.post('{{route('period_meter.detail')}}',{id:id},function(result){
            if
                (result.errorMsg) $.messager.alert('Warning',result.errorMsg);
            else
                $('#editData').find('.data-body').html('');
                $('#editData').find('.data-body').html(result);
        });
        $('.nav-tabs a[href="#tab_2"]').tab('show');

    }
}
function approve(){
    var row = $('#dg').datagrid('getSelected');
    if (row){
        $.messager.confirm('Confirm','Are you sure you want to approve this '+entity+'?',function(r){
            if (r){
                id = row.id;
                $.post('{{route('period_meter.approve')}}',{id:id},function(result){
                    // console.log(result);
                    if (result.success){
                        $.messager.alert('Warning','Approve Success');
                        $('#dg').datagrid('reload');
                    } else {
                        $.messager.alert('Warning',result.errorMsg);
                    }
                },'json');
            }
        });
    }
}

function unposting(){
    var row = $('#dg').datagrid('getSelected');
    if (row){
        $.messager.confirm('Confirm','Are you sure you want to unposting this '+entity+'?',function(r){
            if (r){
                id = row.id;
                $.post('{{route('period_meter.unposting')}}',{id:id},function(result){
                    // console.log(result);
                    if (result.success){
                        $.messager.alert('Warning','Unposting Success');
                        $('#dg').datagrid('reload');
                    } else {
                        $.messager.alert('Warning',result.errorMsg);
                    }
                },'json');
            }
        });
    }
}

function myformatter(date){
    var y = date.getFullYear();
    var m = date.getMonth()+1;
    var d = date.getDate();
    return (d<10?('0'+d):d)+'-'+(m<10?('0'+m):m)+'-'+y;
}
function myparser(s){
    if (!s) return new Date();
    var ss = (s.split('-'));
    var y = parseInt(ss[0],10);
    var m = parseInt(ss[1],10);
    var d = parseInt(ss[2],10);
    if (!isNaN(y) && !isNaN(m) && !isNaN(d)){
        return new Date(d,m-1,y);
    } else {
        return new Date();
    }
}

// $(document).delegate('.numeric', 'keypress', function(e){
// var charCode = (e.which) ? e.which : event.keyCode;
// if ((charCode < 48 || charCode > 57))
//     return false;

// return true;
// });

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
