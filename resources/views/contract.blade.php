@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Unit
@endsection

<!-- page title -->
@section('contentheader_title')
   Contract
@endsection

<!-- tambahan script atas -->
@section('htmlheader_scripts')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/default/easyui.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/icon.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/color.css') }}">
    <!-- select2 -->
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/select2/select2.min.css') }}">
    <style>
    .datagrid-wrap{
        height: 400px;
    }
    </style>
@endsection

@section('contentheader_breadcrumbs')
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Contract</li>
    </ol>
@stop

@section('main-content')
    <div class="container spark-screen">
        <div class="row">
            <div class="col-md-11">
                <!-- content -->

                <!-- template tabel -->
                <table id="dg" title="Contract" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
                    <!-- kolom -->
                    <thead>
                        <tr>
                            <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                            <th field="contr_code" width="120" sortable="true">Contract Code</th>
                            <th field="contr_no" width="120" sortable="true">Contract No</th>
                            <th field="contr_startdate" width="120" sortable="true">Start Date</th>
                            <th field="contr_enddate" width="120" sortable="true">End Date</th>
                            <th field="tenan_name" width="120" sortable="true">Tenant</th>
                            <th field="contr_status" width="120" sortable="true">Status</th>
                            <th field="contr_terminate_date" width="120" sortable="true">Terminated Date</th>
                            <th field="action">Action</th>
                        </tr>
                    </thead>
                </table>
                <!-- end table -->
                
                <!-- icon2 atas table -->
                <div id="toolbar">
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" data-toggle="modal" data-target="#formModal">New</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editUser()">Edit</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="destroyUser()">Remove</a>
                </div>
                <!-- end icon -->
            
                

                <!-- Modal extra -->
                <div id="detailModal" class="modal fade" role="dialog">
                  <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Contract Information</h4>
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

                <!-- modal form -->
                <div id="formModal" class="modal fade" role="dialog">
                  <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Add Contract</h4>
                      </div>
                      <div class="modal-body">
                        <form>
                            <div class="form-group">
                                <label>Contract Parent</label>
                                <select class="form-control contract-parent" style="width:100%">
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Contract Code</label>
                                <input type="text" name="contr_code" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Contract No</label>
                                <input type="text" name="contr_no" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Contract Start Date</label>
                                <input type="text" name="contr_no" class="form-control">
                            </div>
                        </form>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                      </div>
                    </div>

                  </div>
                </div>
                <!-- end modal form -->

                <!-- content -->
            </div>
        </div>
    </div>
@endsection

@section('footer-scripts')
<script src="{{asset('plugins/jQueryUI/jquery-ui.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('plugins/jquery-easyui/jquery.easyui.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/datagrid-filter.js') }}"></script>
<!-- select2 -->
<script type="text/javascript" src="{{ asset('plugins/select2/select2.min.js') }}"></script>

<script type="text/javascript">
        $(".contract-parent").select2({
              ajax: {
                url: "{{route('contract.optParent')}}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                  return {
                    q: params.term, // search term
                    page: params.page
                  };
                },
                
                cache: true
              },
              escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
              minimumInputLength: 1
        });

        var entity = "Contract"; // nama si tabel, ditampilin di dialog
        var get_url = "{{route('contract.get')}}";
        var insert_url = "{{route('unit.insert')}}";
        var update_url = "{{route('unit.update')}}";
        var delete_url = "{{route('unit.delete')}}";

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

        $(document).delegate('.getDetail','click',function(){
            $('#detailModalContent').html('<center><img src="{{ asset('img/loading.gif') }}"><p>Loading ...</p></center>');
            var id = $(this).data('id');
            $.post('{{route('contract.getdetail')}}',{id:id}, function(data){
                $('#detailModalContent').html(data);
            });
        });
</script>
@endsection
