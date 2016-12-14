@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Payment
@endsection

<!-- page title -->
@section('contentheader_title')
   Payment
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
    .datepicker{z-index:999 !important;}

    .pagination > li > span{
      padding-bottom: 9px;
    }
    </style>
@endsection

@section('contentheader_breadcrumbs')
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Payment</li>
    </ol>
@stop

@section('main-content')
    <div class="container spark-screen">
        <div class="row">
            <div class="col-md-11">

          <!-- Tabs -->
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#tab_1" data-toggle="tab">Lists</a></li>
              <li><a href="#tab_2" data-toggle="tab">Add Payment</a></li>
              <li class="hidden"><a href="#tab_3" data-toggle="tab">Edit Payment</a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="tab_1">
                  <!-- template tabel -->
                <table id="dg" title="Payment" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
                    <!-- kolom -->
                    <thead>
                        <tr>
                            <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                            <th field="tenan_name" width="120" sortable="true">Tenant</th>
                            <th field="contr_no" width="120" sortable="true">Payment Code</th>
                            <th field="invpayh_checkno" width="120" sortable="true">Payment Code</th>
                            <th field="invpayh_date" width="120" sortable="true">Tanggal Bayar</th>
                            <th field="invpayh_amount" width="120" sortable="true">Total Pembayaran</th>
                            
                            <th field="invpayh_post" width="120" sortable="true">Status</th>
                            <th field="action_button">Action</th>
                        </tr>
                    </thead>
                </table>
                <!-- end table -->
                
                
              </div>
              <!-- /.tab-pane -->
              <div class="tab-pane" id="tab_2">
                <div id="contractStep1">
                  <form method="POST" id="formPayment">
                      
                      <div class="form-group">
                          <label>Tenan Name</label>
                          <select class="form-control contrId choose-style" name="contr_id" style="width:100%">
                            <option value="">-</option>
                            <?php
                                foreach ($contract_data as $key => $value) {
                            ?>
                            <option value="<?php echo $value['id']?>"><?php echo $value['tenan_name']?></option>
                            <?php
                                }
                            ?>
                          </select>
                      </div>

                      <div class="form-group">
                          <label>Payment Code</label>
                          <input type="text" name="invpayh_checkno" class="form-control">
                      </div>
                      
                      <div class="row">
                        <div class="col-sm-6">
                          <div class="form-group">
                              <label>Bank</label>
                              <select class="form-control cashbkId choose-style" name="cashbk_id" style="width:100%">
                                <option value="">-</option>
                                <?php
                                    foreach ($cashbank_data as $key => $value) {
                                ?>
                                <option value="<?php echo $value['id']?>"><?php echo $value['cashbk_name']?></option>
                                <?php
                                    }
                                ?>
                              </select>
                          </div>
                        </div>
                        <div class="col-sm-6">
                          <div class="form-group">
                              <label>Payment Type</label>
                              <select class="form-control paymtpCode choose-style" name="paymtp_code" style="width:100%">
                                <option value="">-</option>
                                <?php
                                    foreach ($payment_type_data as $key => $value) {
                                ?>
                                <option value="<?php echo $value['id']?>"><?php echo $value['paymtp_name']?></option>
                                <?php
                                    }
                                ?>
                              </select>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                         <div class="col-sm-6">
                            <div class="form-group">
                                <label>Payment Date</label>
                                <div class="input-group date">
                                  <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                  </div>
                                  <input type="text" id="invpayhDate" name="invpayh_date" required="required" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd">
                                </div>
                            </div>
                         </div>
                         <div class="col-sm-6">
                            <div class="form-group">
                                <label>Giro Date</label>
                                <div class="input-group date">
                                  <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                  </div>
                                  <input type="text" id="invpayhGiro" name="invpayh_giro" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd">
                                </div>
                            </div>
                         </div>
                      </div>    

                      <div class="form-group">
                          <label>Note</label>
                          <input type="text" name="invpayh_note" class="form-control">
                      </div>

                      <div class="ajax-detail"></div>

                      <div class="checkbox">
                        <label>
                          <input type="checkbox" name="invpayh_post"> Posting Payment
                        </label>
                      </div>
                      
                      <button type="submit" class="btn btn-primary">submit</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
       


                <!-- content -->
            
                <!-- Modal extra -->
                <div id="detailModal" class="modal fade" role="dialog">
                  <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Payment Information</h4>
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

                <!-- Modal select unit -->
                <div id="unitModal" class="modal fade" role="dialog">
                  <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                      
                      <div class="modal-body" id="unitModalContent">
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                      </div>
                    </div>

                  </div>
                </div>
                <!-- End Modal -->
                

                <!-- modal form -->
                <div id="editModal" class="modal fade" role="dialog">
                  <div class="modal-dialog" style="width:80%">

                    <!-- Modal content-->
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Edit Payment</h4>
                      </div>
                      <div class="modal-body">
                        
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
<!-- datepicker -->
<script type="text/javascript" src="{{ asset('plugins/datepicker/bootstrap-datepicker.js') }}"></script>

<script type="text/javascript">
    var entity = "Payment"; // nama si tabel, ditampilin di dialog
    var get_url = "{{route('payment.get')}}";

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

    $('#formPayment').submit(function(e){
        e.preventDefault();
        var startdate = $('#invpayhDate').val();
        
        if(startdate == ''){
            $.messager.alert('Warning','Payment date must be choose');
        }else if($('#contrId').val() == ""){
          $.messager.alert('Warning','Payment must be choose');
        }else if($('#cashbkId').val() == ""){
          $.messager.alert('Warning','Bank must be choose');
        }else if($('#paymtpCode').val() == ""){
          $.messager.alert('Warning','Payment type must be choose');
        }

        var allFormData = $('#formPayment').serialize();
        
        $.post('{{route('payment.insert')}}',allFormData, function(result){
            alert(result.message);
            if(result.status == 1) location.reload();
        });

        return false;
    });

    $('.datepicker').datepicker({
        autoclose: true
    });

    $(".choose-style").select2();

    $(".contrId").change(function(){
        var url = "{{route('payment.get_invoice')}}";
        var val = $(this).val();

        url = url+"?contract_id="+val;

        $.ajax({
            url: url,
            type: 'GET',
            success: function(result) {
                $('.ajax-detail').html(result);

                return false;
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert('failed during proccess.');
                return false;
            }
        });
    });

    $(document).delegate('.getDetail','click',function(){
        $('#detailModalContent').html('<center><img src="{{ asset('img/loading.gif') }}"><p>Loading ...</p></center>');
        var id = $(this).data('id');
        $.post('{{route('payment.getdetail')}}',{id:id}, function(data){
            $('#detailModalContent').html(data);
        });
    });
</script>
@endsection
