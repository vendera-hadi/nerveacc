@extends('layouts.app')

@section('htmlheader_title')
    Purchase Order
@endsection

@section('contentheader_title')
   Purchase Order
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
        <li class="active">Purchase Order</li>
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
        <li  class="active"><a href="#">Lists</a></li>
        <li><a href="{{route('po.add')}}">Insert PO</a></li>
      </ul>

      <div class="tab-content">
        <div class="tab-pane active" id="tab_1">
            <!-- template tabel -->

            <table id="dg" title="Purchase Orders" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
              <!-- kolom -->
              <thead>
                  <tr>
                      <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                      <th field="po_number" width="100" sortable="true">PO Number</th>
                      <th field="po_date" width="100" sortable="true">PO Date</th>
                      <th field="due_date" width="100" sortable="true">Due Date</th>
                      <th field="spl_name" width="100" sortable="true">Supplier Name</th>
                      <th field="action_button" width="30">Action</th>
                  </tr>
              </thead>
          </table>
          <!-- end table -->

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
    var entity = "Purchase Order"; // nama si tabel, ditampilin di dialog
    var get_url = "{{route('po.get')}}";

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

        // remove po
          $(document).delegate('.remove','click',function(){
                if(confirm('Are you sure want to remove this?')){
                    var id = $(this).data('id');
                    $.post('{{route('po.delete')}}', {id:id}, function(result){
                        if(result.errorMsg) $.messager.alert('Warning',result.errorMsg);
                        if(result.success){
                            $.messager.alert('Warning',result.message);
                            location.reload();
                        }
                    });
                }
           });
    });

    $('.datepicker').datepicker({
        autoclose: true
    });


</script>
@endsection