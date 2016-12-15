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
        <li><a href="{{route('payment.index')}}">Payment</a></li>
        <li class="active">Edit Payment</li>
    </ol>
@stop

@section('main-content')
<?php
    $invpayh_date = $invoice['invpayh_date'];
    $invpayh_checkno = $invoice['invpayh_checkno'];
    $invpayh_giro = $invoice['invpayh_giro'];
    $invpayh_note = $invoice['invpayh_note'];
    $invpayh_post = !empty($invoice['invpayh_post']) ? true : false;
    $paymtp_code = $invoice['paymtp_code'];
    $cashbk_id = $invoice['cashbk_id'];
    $contr_id = $invoice['contr_id'];
?>
    <div class="container spark-screen">
        <div class="row">
            <div class="col-md-11">

          <!-- Tabs -->
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#tab_1" data-toggle="tab">Edit Payment</a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="tab_1">
                  <!-- template tabel -->
                <form method="POST" id="formPayment">
                      <input type="hidden" name="invoice_paymhdr_id" value="<?php echo $invoice['id']?>">
                      <div class="form-group">
                          <label>Tenan Name : </label>
                          <?php
                              printf('%s | %s', $invoice['tr_contract']['ms_tenant']['tenan_name'], $invoice['tr_contract']['contr_code']);
                          ?>
                      </div>

                      <div class="form-group">
                          <label>Payment Code</label>
                          <input type="text" name="invpayh_checkno" class="form-control" value="<?php echo $invpayh_checkno;?>">
                      </div>
                      
                      <div class="row">
                        <div class="col-sm-6">
                          <div class="form-group">
                              <label>Bank</label>
                              <select class="form-control cashbkId choose-style" name="cashbk_id" style="width:100%">
                                <option value="">-</option>
                                <?php
                                    foreach ($cashbank_data as $key => $value) {
                                      $selected = ($cashbk_id == $value['id']) ? 'selected="selected"' : '';
                                ?>
                                <option <?php echo $selected;?> value="<?php echo $value['id']?>"><?php echo $value['cashbk_name'];?></option>
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
                                      $selected = ($paymtp_code == $value['id']) ? 'selected="selected"' : '';
                                ?>
                                <option <?php echo $selected;?> value="<?php echo $value['id']?>"><?php echo $value['paymtp_name'];?></option>
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
                                  <input type="text" id="invpayhDate" name="invpayh_date" required="required" class="form-control pull-right datepicker" value="<?php echo $invpayh_date;?>" data-date-format="yyyy-mm-dd">
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
                                  <input type="text" id="invpayhGiro" name="invpayh_giro" class="form-control pull-right datepicker" value="<?php echo $invpayh_giro;?>" data-date-format="yyyy-mm-dd">
                                </div>
                            </div>
                         </div>
                      </div>    

                      <div class="form-group">
                          <label>Note</label>
                          <input type="text" name="invpayh_note" class="form-control" value="<?php echo $invpayh_note;?>">
                      </div>

                      <div class="ajax-detail">
                        <div class="table-responsive">
                          <table class="table table-hover table-bordered">
                              <thead>
                                  <tr>
                                      <th width="100">No.Invoice</th>
                                      <th width="100">Unit</th>  
                                      <th width="50">Tgl Invoice</th>
                                      <th width="50">Jatuh Tempo</th>
                                      <th width="80">Outstanding Amount</th> 
                                      <th width="80" ></th> 
                                  </tr>
                              </thead>
                              <tbody>
                                  <?php
                                      if(!empty($invoice['tr_invoice_paymdtl'])){
                                          foreach ($invoice['tr_invoice_paymdtl'] as $key => $value) {
                                              $invpayd_amount = $value['invpayd_amount'];
                                              $tr_invoice = $value['tr_invoice'];

                                              $inv_duedate = strtotime($tr_invoice['inv_duedate']);
                                              $inv_date = strtotime($tr_invoice['inv_date']);
                                              $inv_id = $tr_invoice['id'];
                                  ?>
                                  <tr>
                                      <td><?php echo $tr_invoice['inv_number'];?></td>
                                      <td><?php echo sprintf('%s %s', $invoice['tr_contract']['ms_unit']['unit_name'], $invoice['tr_contract']['ms_unit']['ms_floor']['floor_name']);?></td>
                                      <td><?php echo date('d/m/y', $inv_date);?></td>
                                      <td><?php echo date('d/m/y', $inv_duedate);?></td>
                                      <td><?php echo 'Rp. '.$tr_invoice['inv_outstanding'];?></td>
                                      <td>
                                          <div class="input-group">
                                              <div class="input-group-addon">Rp</div>
                                              <input type="text" name="data_payment[<?php echo $value['id'];?>][invpayd_amount]" class="form-control" value="<?php echo $tr_invoice['inv_outstanding'];?>">
                                              <input type="hidden" name="data_payment[<?php echo $value['id'];?>][inv_id]" value="<?php echo $inv_id;?>">
                                          </div>
                                      </td>
                                  </tr>
                                  <?php
                                          }
                                      }else{
                                  ?>
                                  <tr>
                                      <td colspan="6">Data not found</td>
                                  </tr>
                                  <?php
                                      }
                                  ?>
                              </tbody>
                          </table>
                      </div>
                      </div>

                      <div class="checkbox">
                        <label>
                          <input type="checkbox" name="invpayh_post" value="<?php echo $invpayh_post;?>"> Posting Payment
                        </label>
                      </div>
                      
                      <button type="submit" class="btn btn-primary">submit</button>
                  </form>
                
                
              </div>
            </div>
          </div>

                <!-- content -->
            </div>
        </div>
    </div>
@endsection

@section('footer-scripts')
<script src="{{asset('plugins/jQueryUI/jquery-ui.min.js')}}"></script>
<!-- select2 -->
<script type="text/javascript" src="{{ asset('plugins/select2/select2.min.js') }}"></script>
<!-- datepicker -->
<script type="text/javascript" src="{{ asset('plugins/datepicker/bootstrap-datepicker.js') }}"></script>

<script type="text/javascript">
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

        var allFormData = $['#formPayment'].serialize();
        
        $.post('{{route('payment.do_edit')}}',allFormData, function(result){
            alert(result.message);
            if(result.status == 1) window.location = '{{route('payment.index')}}' ;
        });

        return false;
    });

    $('.datepicker').datepicker({
        autoclose: true
    });

    $(".choose-style").select2();
</script>
@endsection
