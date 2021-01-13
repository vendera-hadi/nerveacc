@extends('layouts.app')

@section('htmlheader_title')
    Account Payable
@endsection

@section('contentheader_title')
   Account Payable
@endsection

@section('htmlheader_scripts')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/default/easyui.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/icon.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/color.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/select2/select2.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/datepicker/datepicker3.css') }}">
    <style>
    .datagrid-wrap{
        height: 400px;
    }
    .datepicker{z-index:999 !important;}

    .pagination > li > span{
      padding-bottom: 9px;
    }

    .loadingScreen{
        position: absolute;
        width: 100%;
        height: 100%;
        background: black;
        z-index: 100;
        background: rgba(204, 204, 204, 0.5);
    }
    </style>
@endsection

@section('contentheader_breadcrumbs')
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Account Payable</li>
    </ol>
@stop

@section('main-content')
<div class="row">
    <div class="col-md-12">
        <div class="loadingScreen" style="display:none">
        <h3 style="line-height: 400px; text-align: center;">LOADING</h3>
        </div>
        <!-- Tabs -->
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab_1" data-toggle="tab">Lists</a></li>
                <li><a href="{{route('payable.withoutpo')}}">Non PO</a></li>
                <li><a href="{{route('payable.withpo')}}">With PO</a></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane active" id="tab_1">
                    <form id="search">
                        <div class="row">
                            <div class="col-sm-5">
                                <div class="form-group">
                                    <div class="input-group date">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input type="text" id="startDate" name="date_from" placeholder="From" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-5">
                                <div class="form-group">
                                    <div class="input-group date">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input type="text" id="startDate" name="date_to" placeholder="To" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <button class="btn btn-flat btn-info btn-block pull-left">Cari</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <table id="dg" title="Account Payable" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
                        <thead>
                            <tr>
                                <th field="checkbox" width="25"></th>
                                <th field="spl_name" sortable="true">Supplier</th>
                                <th field="invoice_no" sortable="true">Invoice No</th>
                                <th field="invoice_date" width="50" sortable="true">Invoice Date</th>
                                <th field="invoice_duedate" sortable="true">Due Date</th>
                                <th field="total" width="50" sortable="true">Amount</th>
                                <th field="posting" sortable="true">Posted</th>
                                <th field="po_no" sortable="true">PO No</th>
                                <th field="action_button">Action</th>
                            </tr>
                        </thead>
                    </table>
                    <div id="toolbar" class="datagrid-toolbar">
                        <label style="margin-left:10px; margin-right:5px"><input type="checkbox" name="checkall" style="vertical-align: top;margin-right: 6px;"><span style="vertical-align: middle; font-weight:400">Check All</span></label>
                        @if(Session::get('role')==1 || in_array(70,Session::get('permissions')))
                        <a href="javascript:void(0)" class="easyui-linkbutton l-btn l-btn-small l-btn-plain" plain="true" onclick="postingInv()" group="" id=""><span class="l-btn-text"><i class="fa fa-check"></i>&nbsp;Posting Selected</span></a>
                        <a href="javascript:void(0)" class="easyui-linkbutton l-btn l-btn-small l-btn-plain" plain="true" onclick="unpostingInv()" group="" id=""><span class="l-btn-text"><i class="fa fa-remove"></i>&nbsp;Unposting Selected AP</span>
                        </a>
                        @endif
                    </div>
                    <div id="dlg" class="easyui-dialog" style="width:40%"
                        closed="true" buttons="#dlg-buttons">
                        <form id="fm" method="post" novalidate style="margin:0;padding:20px 50px">
                            <div style="margin-bottom:10px">
                                <input type="text" class="easyui-datebox" required="required" name="posting_date" label="Posting Date :" style="width:100%" data-options="formatter:myformatter,parser:myparser">
                            </div>
                        </form>
                    </div>
                    <div id="dlg-buttons">
                        <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="savepayment()" style="width:90px">Save</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg').dialog('close')" style="width:90px">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- modal -->
<div id="detailModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content" style="width:100%">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Account Payable Detail</h4>
            </div>
            <div class="modal-body" id="detailModalContent"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- end modal -->

@endsection

@section('footer-scripts')
<script src="{{asset('plugins/jQueryUI/jquery-ui.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('plugins/jquery-easyui/jquery.easyui.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/datagrid-filter.js') }}"></script>
<script type="text/javascript" src="{{ asset('plugins/select2/select2.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('plugins/datepicker/bootstrap-datepicker.js') }}"></script>

<script type="text/javascript">
    var entity = "Account Payable";
    var get_url = "{{route('payable.get')}}";

    $(function(){
        var dg = $('#dg').datagrid({
            url: get_url,
            pagination: true,
            remoteFilter: true,
            rownumbers: true,
            singleSelect: true,
            fitColumns: true,
            pageSize:100,
            pageList: [100,500,1000],
        });
        dg.datagrid('enableFilter');

        $(".js-example-basic-single").select2();

        $(document).delegate('.remove','click',function(){
              if(confirm('Are you sure want to remove this?')){
                  var id = $(this).data('id');
                  $.post('{{route('payable.delete')}}', {id:id}, function(result){
                      if(result.errorMsg) $.messager.alert('Warning',result.errorMsg);
                      if(result.success){
                          $.messager.alert('Warning',result.message);
                          location.reload();
                      }
                  });
              }
         });

        $('input[name=checkall]').change(function() {
            if($(this).is(':checked')){
                $('input[name=check]').each(function(){
                    $(this).prop('checked',true);
                });
            }else{
                $('input[name=check]').each(function(){
                    $(this).prop('checked',false);
                });
            }
         });

        $(document).delegate('.detail', 'click', function(){
            var id = $(this).data('id');
            $.post('{{route('payable.detail')}}', {id:id}, function(result){
                $('#detailModal').modal('show');
                $('#detailModalContent').html(result);
            });
        });

    });

    var query, invtype, from, to, outstanding;
    $('#search').submit(function(e){
        e.preventDefault();

        datefrom = $(this).find('input[name=date_from]').val();
        dateto = $(this).find('input[name=date_to]').val();

        if((datefrom == "" && dateto !="") || (datefrom!="" && dateto=="")){
            alert('Isi kedua tanggal From dan To untuk melakukan filter tanggal');
        }else{

            if(datefrom!="" && dateto!=""){
                var dateFirst = datefrom.split('-');
                var dateSecond = dateto.split('-');
                var from = new Date(dateFirst[2], dateFirst[1], dateFirst[0]); //Year, Month, Date
                var to = new Date(dateSecond[2], dateSecond[1], dateSecond[0]);

                if(to<=from){
                    alert('Tanggal To harus lebih lama dari Tanggal From');
                    return false;
                }
            }

            $('#dg').datagrid('load', {
                datefrom: datefrom,
                dateto: dateto
            });
            $('#dg').datagrid('reload');
        }
    });

    $('.datepicker').datepicker({
        autoclose: true
    });

    function postingAll(){
        // var row = $('#dg').datagrid('getSelected');
        var ids = [];
        $('input[name=check]:checked').each(function() {
           if($(this).data('posting') == "") ids.push($(this).val());
        });
        // if(row.inv_post == 'no'){
        if(ids.length > 0){
            $.messager.confirm('Confirm','Are you sure you want to post this '+ids.length+' unposted AP ?',function(r){
                if (r){
                    $('.loadingScreen').show();
                    // posting invoice
                    $.post('{{route('payable.posting')}}',{id:ids},function(result){
                        console.log(result);
                        $('.loadingScreen').hide();
                        if(result.error){
                            $.messager.alert('Warning',result.message);
                        }
                        if(result.success){
                            $.messager.alert('Success',result.message);
                            $('#dg').datagrid('reload');
                            //location.reload();
                        }
                    },'json');
                }
            });
        }
    }

    function unpostingInv(){
        var ids = [];
        $('input[name=check]:checked').each(function() {
           ids.push($(this).val());
        });
        if(ids.length > 0){
            $.messager.confirm('Confirm','Are you sure you want to unpost this '+ids.length+' data ?',function(r){
                if (r){
                    $('.loadingScreen').show();
                    $.post('{{route('payable.unposting')}}',{id:ids}, function(data){
                        alert(data.message);
                        $('.loadingScreen').hide();
                        if(data.success == 1){
                            $('#dg').datagrid('reload');
                        } else{
                            return false;
                        }
                    });
                }
            });
        }
    }

    function postingInv(){
        $('#dlg').dialog('open').dialog('center').dialog('setTitle','Posting Payment');
        $('#fm').form('clear');
    }

    function savepayment(){
        var ids = [];
        $('input[name=check]:checked').each(function() {
           ids.push($(this).val());
        });
        if(ids.length > 0){
            var data = $("#fm").serializeArray();
            data.push({name: "id", value: ids});
            $.post('{{route('payable.posting')}}',data, function(result){
                if(result.success == 1){
                    $.messager.alert('Success','Data saved');
                    $('#dlg').dialog('close');
                    $('#dg').datagrid('reload');
                }else{
                    $.messager.show({
                        title: 'Error',
                        msg: result.message
                    });
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
</script>
@endsection