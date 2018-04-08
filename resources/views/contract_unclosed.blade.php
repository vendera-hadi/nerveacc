@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Reminder Unclosed Billing
@endsection

<!-- page title -->
@section('contentheader_title')
   Reminder Billing
@endsection

<!-- tambahan script atas -->
@section('htmlheader_scripts')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/default/easyui.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/icon.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/color.css') }}">
    <!-- select2 -->
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/select2/select2.min.css') }}">
    <!-- datepicker -->
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/datepicker/datepicker3.css') }}">
    <style>
    .datagrid-wrap{
        height: 400px;
    }
    .datepicker{z-index:1511 !important;}

    .pagination > li > span{
      padding-bottom: 9px;
    }
    </style>
@endsection

@section('contentheader_breadcrumbs')
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Reminder Billing</li>
    </ol>
@stop

@section('main-content')
    <div class="row">
        <div class="col-md-12">
            @if(Session::get('success'))
                <div class="alert alert-success">
                  <strong>Success</strong> {{ Session::get('success') }}
                </div>
            @endif

            <!-- template tabel -->
            <table id="dg" title="Billing Info" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
                <!-- kolom -->
                <thead>
                    <tr>
                        <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                        <th field="tenan_name" width="120" sortable="true">Tenant</th>
                        <th field="contr_code" width="120" sortable="true">Billing Info Code</th>
                        <th field="contr_no" width="120" sortable="true">Billing Info No</th>
                        <th field="contr_enddate" width="120" sortable="true">End Date</th>
                        <th field="contr_status" width="120" sortable="true">Status</th>
                        <th field="contr_terminate_date" width="120" sortable="true">Terminated Date</th>
                        <th field="action">Action</th>
                    </tr>
                </thead>
            </table>
            <!-- end table -->

            <!-- Modal extra -->
            <div id="detailModal" class="modal fade" role="dialog">
              <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Billing Info Information</h4>
                  </div>
                  <div class="modal-body" id="detailModalContent">
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                  </div>
                </div>

              </div>
            </div>
            <!-- End Modal -->


            <div id="closeCtrModal" class="modal fade" role="dialog">
              <div class="modal-dialog" style="width: 850px;">

                <!-- Modal content-->
                <div class="modal-content" style="width: 850px;">

                  <div class="modal-body text-center" id="closeCtrModalContent">
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                  </div>
                </div>

              </div>
            </div>

        <!-- content -->
        </div>
    </div>
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
        var entity = "Unclosed Billing Info"; // nama si tabel, ditampilin di dialog
        var get_url = "{{route('contract.getunclosed')}}";

        $(function(){
            var dg = $('#dg').datagrid({
                url: get_url,
                pagination: true,
                remoteFilter: true, //utk jalanin search filter
                rownumbers: true,
                singleSelect: true,
                fitColumns: true,
                rowStyler:function(index,row){
                    if(!isEmpty(row)){
                        // priority 1 terminate
                        if(row.terminate_diff > -4 && !isNaN(parseInt(row.terminate_diff)) ){
                            return 'background-color:red';
                        }else if(row.terminate_diff > -8 && row.terminate_diff <= -4 && !isNaN(parseInt(row.terminate_diff)) ){
                            return 'background-color:yellow';
                        }else if (row.terminate_diff > -31 && row.terminate_diff <= -8 && !isNaN(parseInt(row.terminate_diff)) ){
                            return 'background-color:lightyellow';
                        }
                        // priority 2 end date
                        if(row.enddate_diff > -4 && !isNaN(parseInt(row.enddate_diff)) ){
                            return 'background-color:red';
                        }else if(row.enddate_diff > -8 && row.enddate_diff <= -4 && !isNaN(parseInt(row.enddate_diff))){
                            return 'background-color:yellow';
                        }else if (row.enddate_diff > -31 && row.enddate_diff <= -8 && !isNaN(parseInt(row.enddate_diff)) ){
                            return 'background-color:lightyellow';
                        }
                    }
                }
            });
            dg.datagrid('enableFilter');
        });

        $('.datepicker').datepicker({
            autoclose: true
        });

        // Speed up calls to hasOwnProperty
        var hasOwnProperty = Object.prototype.hasOwnProperty;

        function isEmpty(obj) {
            // null and undefined are "empty"
            if (obj == null) return true;
            // Assume if it has a length property with a non-zero value
            // that that property is correct.
            if (obj.length > 0)    return false;
            if (obj.length === 0)  return true;
            // If it isn't an object at this point
            // it is empty, but it can't be anything *but* empty
            // Is it empty?  Depends on your application.
            if (typeof obj !== "object") return true;
            // Otherwise, does it have any properties of its own?
            // Note that this doesn't handle
            // toString and valueOf enumeration bugs in IE < 9
            for (var key in obj) {
                if (hasOwnProperty.call(obj, key)) return false;
            }
            return true;
        }

        $(document).delegate('.getDetail','click',function(){
            $('#detailModalContent').html('<center><img src="{{ asset('img/loading.gif') }}"><p>Loading ...</p></center>');
            var id = $(this).data('id');
            $.post('{{route('contract.getdetail')}}',{id:id}, function(data){
                $('#detailModalContent').html(data);
            });
        });

        $(document).delegate('.closeContract','click',function(){
            var id = $(this).data('id');
            console.log(id);
            $.post('{{route('contract.closeCtrModal')}}',{id:id}, function(data){
                $('#closeCtrModalContent').html(data);
            });
        });
</script>
@endsection