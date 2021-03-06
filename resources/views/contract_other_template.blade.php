@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Billing Info {{$pageType}}
@endsection

<!-- page title -->
@section('contentheader_title')
   Billing Info {{$pageType}}
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
        <li class="active">Billing Info {{$pageType}}</li>
    </ol>
@stop

@section('main-content')
    <div class="row">
        <div class="col-md-12">
            <!-- template tabel -->
            <table id="dg" title="Billing Info" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
                <!-- kolom -->
                <thead>
                    <tr>
                        <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                        @if($pageType == 'Confirmation')
                        <th field="checkbox" width="25"></th>
                        @endif
                        <th field="unit_code" width="120" sortable="true">Unit</th>
                        <th field="tenan_name" width="120" sortable="true">Tenant</th>
                        <th field="contr_startdate" width="120" sortable="true">Start Date</th>
                        <th field="contr_enddate" width="120" sortable="true">End Date</th>
                        
                        <th field="contr_status" width="120" sortable="true">Status</th>
                        <th field="contr_terminate_date" width="120" sortable="true">Terminated Date</th>
                        <th field="action">Action</th>
                    </tr>
                </thead>
            </table>
            <!-- end table -->
            @if($pageType == 'Confirmation')
            <div id="toolbar" class="datagrid-toolbar">
                <label style="margin-left:10px; margin-right:5px"><input type="checkbox" name="checkall" style="vertical-align: top;margin-right: 6px;"><span style="vertical-align: middle; font-weight:400">Check All</span></label>
              <a href="javascript:void(0)" class="easyui-linkbutton l-btn l-btn-small l-btn-plain" plain="true" onclick="confirmMany()" group="" id=""><span class="l-btn-text"><i class="fa fa-check"></i>&nbsp;Confirm Selected</span></a>
            </div>
            @endif

            <div id="addendumModal" class="modal fade" role="dialog">
              <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                  
                  <div class="modal-body text-center" id="addendumModalContent">
                    <h3>Input Reason :</h3>
                    <form method="post" id="addendumForm">
                        <input type="hidden" name="id" id="addendumId">
                        <br><br>
                        <center><textarea name="note" class="form-control" style="width:50%" required></textarea></center>
                        <br>
                        <button class="btn btn-info">Submit</button>
                    </form>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                  </div>
                </div>

              </div>
            </div>

             <div id="terminateModal" class="modal fade" role="dialog">
              <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                  
                  <div class="modal-body text-center">
                    <h3>Input Terminate Date :</h3>
                    <form method="post" id="terminateForm">
                        <input type="hidden" name="id" id="terminateId">
                        <br><br>
                        <center>
                            <input type="text" name="contr_terminate_date" class="form-control datepicker" style="width:50%" data-date-format="yyyy-mm-dd" required>
                        </center>
                        <br>
                        <button class="btn btn-info">Submit</button>
                    </form>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                  </div>
                </div>

              </div>
            </div>

            <div id="renewModal" class="modal fade" role="dialog">
              <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                  
                  <div class="modal-body text-center" id="renewModalContent">
                    <h3>Input New Data :</h3>
                    <form method="post" id="renewForm">
                        <input type="hidden" name="id" id="renewId">
                        
                        <div class="form-group">
                            <label>Billing Info Start Date</label>
                            <div class="input-group date">
                              <div class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                              </div>
                              <input type="text" id="startDate" name="contr_startdate" required="required" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd" >
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Billing Info End Date</label>
                            <div class="input-group date">
                              <div class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                              </div>
                              <input type="text" id="endDate" name="contr_enddate" required="required" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd" >
                            </div>
                        </div>
                        <button type="submit" class="btn btn-default">Submit</button>
                    </form>
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
        var entity = "Billing Info {{$pageType}}"; // nama si tabel, ditampilin di dialog
        var get_url = "{{route('contract.getother',['page'=>$pageType])}}";

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

        $('.datepicker').datepicker({
            autoclose: true
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

        function confirmMany(){
            var ids = [];
            $('input[name=check]:checked').each(function() {
               ids.push($(this).val());
            });
            if(ids.length > 0){
              $.messager.confirm('Confirm','Are you sure you want to confirm this '+ids.length+' Billing Information ?',function(r){
                  if (r){
                      $.post('{{route('contract.confirm')}}',{id:ids},function(result){
                            if(result.errorMsg) $.messager.alert('Warning',result.errorMsg);
                            if(result.success){ 
                                $.messager.alert('Warning','Status updated to Confirmed');
                                location.reload();
                            }
                        });
                  }
              });
            }
        }

        $(document).delegate('.confirmStatus','click',function(){
            var r = confirm("Are you sure want to change this status to 'Confirmed' ?");
            if(r == true){
                var id = $(this).data('id');
                $.post('{{route('contract.confirm')}}',{id:id},function(result){
                    if(result.errorMsg) $.messager.alert('Warning',result.errorMsg);
                    if(result.success){ 
                        $.messager.alert('Warning','Status updated to Confirmed');
                        location.reload();
                    }
                });
            }
        }).delegate('.rollbackStatus','click',function(){
            $('#addendumId').val($(this).data('id'));
            $('#addendumModal').modal('show');
        }).delegate('.terminateStatus','click',function(){
            $('#terminateId').val($(this).data('id'));
            $('#terminateModal').modal('show');
            // var r = confirm("Are you sure want to terminate this Contract ?");
            // if(r == true){
            //     var id = $(this).data('id');
            //     $.post('{{route('contract.terminate')}}',{id:id},function(result){
            //         if(result.errorMsg) $.messager.alert('Warning',result.errorMsg);
            //         if(result.success){ 
            //             $.messager.alert('Warning','Contract Terminated');
            //             location.reload();
            //         }
            //     });
            // }
        }).delegate('.renewStatus','click',function(){
            $('#renewId').val($(this).data('id'));
            // $('#renewForm').find('input[name="contr_code"]').val($(this).data('code'));
            // $('#renewForm').find('input[name="contr_no"]').val($(this).data('no'));
            $('#renewForm').find('input[name="contr_startdate"]').val($(this).data('start'));
            $('#renewForm').find('input[name="contr_enddate"]').val($(this).data('end'));
            $('#renewModal').modal('show');
        });

        $('#addendumForm').submit(function(e){
            e.preventDefault();
            $.post('{{route('contract.inputed')}}',$(this).serialize(),function(result){
                    if(result.errorMsg) $.messager.alert('Warning',result.errorMsg);
                    if(result.success){ 
                        $.messager.alert('Success','Status rolled back to Inputed');
                        location.reload();
                    }
                });
        });

        $('#terminateForm').submit(function(e){
            e.preventDefault();
            $.post('{{route('contract.terminate')}}',$(this).serialize(),function(result){
                    if(result.errorMsg) $.messager.alert('Warning',result.errorMsg);
                    if(result.success){ 
                        $.messager.alert('Success','Contract Terminated');
                        location.reload();
                    }
                });
        });

        $('#renewForm').submit(function(e){
            e.preventDefault();
            $.post('{{route('contract.renew')}}',$(this).serialize(),function(result){
                    console.log(result);
                    if(result.errorMsg) $.messager.alert('Warning',result.errorMsg);
                    if(result.success){ 
                        $.messager.alert('Warning','Contract Renewed Successfully');
                        location.reload();
                    }
                });
        });
</script>
@endsection