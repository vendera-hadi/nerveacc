@extends('layouts.app')

@section('htmlheader_title')
    Bank Book
@endsection

@section('contentheader_title')
   Bank Book
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
        <li class="active">Bank Book</li>
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
        @if(Session::get('role')==1 || in_array(69,Session::get('permissions')))
        <li><a href="#tab_2" data-toggle="tab">Add Transaction</a></li>
        @endif
        <li class="hidden"><a href="#tab_3" data-toggle="tab">Edit Transaction</a></li>
      </ul>
      <div class="tab-content">
        <div class="tab-pane active" id="tab_1">
            <!-- template tabel -->
          <table id="dg" title="Payment" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
              <!-- kolom -->
              <thead>
                  <tr>
                      <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                      <th field="checkbox" width="25"></th>
                      <th field="voucher_no" sortable="true">No Voucher</th>
                      <th field="note" width="50" sortable="true">Note</th>
                      <th field="transaction_date" sortable="true">Date</th>
                      <th field="paymtp_name" width="50" sortable="true">Payment Type</th>
                      <th field="check_date" width="50" sortable="true">Check/Giro Date</th>
                      <th field="amount" sortable="true">Total</th>
                      <th field="is_posted" width="50" sortable="true">Posting Status</th>
                      <th field="action_button">Action</th>
                  </tr>
              </thead>
          </table>
          <!-- end table -->
          
          <!-- icon2 atas table -->
          <div id="toolbar" class="datagrid-toolbar">
              <label style="margin-left:10px; margin-right:5px"><input type="checkbox" name="checkall" style="vertical-align: top;margin-right: 6px;"><span style="vertical-align: middle; font-weight:400">Check All</span></label>
              @if(Session::get('role')==1 || in_array(70,Session::get('permissions')))
              <a href="javascript:void(0)" class="easyui-linkbutton l-btn l-btn-small l-btn-plain" plain="true" onclick="postingInv()" group="" id=""><span class="l-btn-text"><i class="fa fa-check"></i>&nbsp;Posting Selected</span></a>
              @endif
          </div>
          <!-- end icon -->
          
        </div>
        <!-- /.tab-pane -->
        <div class="tab-pane" id="tab_2">
          <div id="contractStep1">
            <form method="POST" id="formPayment">
                
                <div class="form-group">
                    <label>No Voucher</label>
                    <input class="form-control" name="voucher_no" type="text" required>
                </div>

                <div class="form-group">
                    <label>No Giro / Check</label>
                    <input type="text" name="invpayh_checkno" class="form-control">
                </div>
                
                <div class="row">
                  <div class="col-sm-6">
                    <div class="form-group">
                        <label>Bank</label>
                        <select class="form-control cashbkId choose-style" name="cashbk_coa" style="width:100%">
                          <option value="">-</option>
                          <?php
                              foreach ($cashbank_data as $key => $value) {
                          ?>
                          <option value="<?php echo $value['coa_code']?>"><?php echo $value['cashbk_name']?></option>
                          <?php
                              }
                          ?>
                        </select>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group">
                        <label>Payment Type</label>
                        <select class="form-control paymtpCode choose-style" name="paymtp_id" style="width:100%">
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
                          <label>Transaction Date</label>
                          <div class="input-group date">
                            <div class="input-group-addon">
                              <i class="fa fa-calendar"></i>
                            </div>
                            <input type="text" id="invpayhDate" name="transaction_date" required="required" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd">
                          </div>
                      </div>
                   </div>
                   <div class="col-sm-6">
                      <div class="form-group">
                          <label>Cheque/Giro Date</label>
                          <div class="input-group date">
                            <div class="input-group-addon">
                              <i class="fa fa-calendar"></i>
                            </div>
                            <input type="text" id="invpayhGiro" name="check_giro" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd">
                          </div>
                      </div>
                   </div>
                </div>    

                <div class="form-group">
                    <label>Note</label>
                    <input type="text" name="invpayh_note" class="form-control">
                </div>
                <br><br>
                <hr>
                <h3 class="text-center">Transactions</h3><br>
                <div class="row">
                	<div class="col-sm-4">
		              <div class="form-group">
		                <label>Description</label>
		                <input class="form-control" id="addDesc">
		              </div>
		            </div>

                	<div class="col-sm-4">
		              <div class="form-group">
		                <label>Amount</label>
		                <input class="form-control" id="addAmount">
		              </div>
		            </div>

		            <div class="col-sm-4">
		              <label>COA</label>
		              <div class="input-group input-group-md">
		                <select class="js-example-basic-single" id="selectAccount" style="width:100%">
		                  <option value="">Choose Account</option>
		                  @foreach($accounts as $key => $coa)
		                      <option value="{{$coa->coa_code}}" data-name="{{$coa->coa_name}}">{{$coa->coa_code." ".$coa->coa_name}}</option>
		                  @endforeach
		                </select>
		                <span class="input-group-btn">
		                  <button type="button" id="addAccount" class="btn btn-default">Add Line</button>
		                </span>
		              </div>
		            </div>
		            
		        </div>

		        <div class="row">
            <div class="col-sm-12">
              <table id="tableJournal" width="100%" class="table table-bordered">
                <tr>
                  <td>Account Code</td>
                  <td>Account Name</td>
                  <td>Description</td>
                  <td>Amount</td>
                  <td></td>
                </tr>
                
                <tr id="rowEmpty">
                  <td colspan="5"><center>Data Kosong. Pilih account dan Add Line terlebih dulu</center></td>
                </tr>
              </table>
            </div>
          </div>
                
                <button type="submit" id="submitForm" class="btn btn-primary">submit</button>
            </form>
          </div>
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
    var entity = "Payment"; // nama si tabel, ditampilin di dialog
    var get_url = "{{route('bankbook.get')}}";

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
    });

    $('.datepicker').datepicker({
        autoclose: true
    });

    var coacode, coaname, desc, amount;
    $("#addAccount").click(function(){
      coacode = $('#selectAccount option:selected').val();
      if(coacode != ""){
        $('#rowEmpty').hide();
        coaname = $('#selectAccount option:selected').data('name');
        desc = $('#addDesc').val();
        amount = $('#addAmount').val();
        $('#tableJournal').append('<tr><input type="hidden" name="coa_code[]" value="'+coacode+'"><td>'+coacode+'</td><td>'+coaname+'</td><td><input type="hidden" name="description[]" value="'+desc+'">'+desc+'</td><td><input type="hidden" name="amount[]" value="'+amount+'" >'+amount+'</td><td><a href="#" class="removeRow"><i class="fa fa-times text-danger"></i></a></td></tr>');
      }
 });
</script>
@endsection