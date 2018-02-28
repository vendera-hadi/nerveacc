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
            <!-- template tabel -->
          <table id="dg" title="Account Payable" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
              <!-- kolom -->
              <thead>
                  <tr>
                      <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                      <th field="checkbox" width="25"></th>
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
          <!-- end table -->

          <!-- icon2 atas table -->
          <div id="toolbar" class="datagrid-toolbar">
              <label style="margin-left:10px; margin-right:5px"><input type="checkbox" name="checkall" style="vertical-align: top;margin-right: 6px;"><span style="vertical-align: middle; font-weight:400">Check All</span></label>
              @if(Session::get('role')==1 || in_array(70,Session::get('permissions')))
              <a href="javascript:void(0)" class="easyui-linkbutton l-btn l-btn-small l-btn-plain" plain="true" onclick="postingAll()" group="" id=""><span class="l-btn-text"><i class="fa fa-check"></i>&nbsp;Posting Selected</span></a>
              @endif
          </div>
          <!-- end icon -->

        </div>
        </div>
    </div>
          <!-- content -->
@endsection

@section('footer-scripts')
<script src="{{asset('plugins/jQueryUI/jquery-ui.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('plugins/jquery-easyui/jquery.easyui.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/datagrid-filter.js') }}"></script>
<!-- select2 -->
<script type="text/javascript" src="{{ asset('plugins/select2/select2.min.js') }}"></script>
<!-- datepicker -->
<script type="text/javascript" src="{{ asset('plugins/datepicker/bootstrap-datepicker.js') }}"></script>

<script type="text/javascript">
    var entity = "Account Payable"; // nama si tabel, ditampilin di dialog
    var get_url = "{{route('payable.get')}}";

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
            // onLoadSuccess:function(target){
            //     print_window();
            // }
        });
        dg.datagrid('enableFilter');

        $(".js-example-basic-single").select2();

        // remove bankbook
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
                            // $('#dg').datagrid('reload');
                            location.reload();
                        }
                    },'json');
                }
            });
        }
        // }else{
        //     $.messager.alert('Warning', 'You can\'t post invoice that already posted');
        // }
    }
</script>
@endsection