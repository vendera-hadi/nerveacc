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
        <li><a href="{{route('bankbook.transfer')}}">Transfer Uang</a></li>
        <li><a href="{{route('bankbook.deposit')}}">Terima Uang</a></li>
        <li><a href="{{route('bankbook.withdraw')}}">Kirim Uang</a></li>
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
                      <th field="trbank_no" sortable="true">No Voucher</th>
                      <th field="trbank_note" width="50" sortable="true">Note</th>
                      <th field="trbank_date" sortable="true">Date</th>
                      <th field="cashbk_name" width="50" sortable="true">Nama Bank</th>
                      <th field="trbank_in" sortable="true">Total In</th>
                      <th field="trbank_out" sortable="true">Total Out</th>
                      <th field="trbank_post" width="50" sortable="true">Posting Status</th>
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
                
                <div class="row">
                  <div class="col-sm-4">
	                <div class="form-group">
	                    <label>No Voucher</label>
	                    <input class="form-control" name="trbank_no" type="text" required>
	                </div>
	               </div>

	               <div class="col-sm-4">
                      <div class="form-group">
                          <label>Transaction Date</label>
                          <div class="input-group date">
                            <div class="input-group-addon">
                              <i class="fa fa-calendar"></i>
                            </div>
                            <input type="text" id="invpayhDate" name="trbank_date" required="required" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd">
                          </div>
                      </div>
                   </div>

                   <div class="col-sm-4">
                   <div class="form-group">
	                    <label>Recipient</label>
	                    <input class="form-control" name="trbank_recipient" type="text" required>
	                </div>
	               </div>

                </div>

                <div class="row">
                  <div class="col-sm-4">
	                <div class="form-group">
	                	<label>Group</label>
	                    <select class="form-control" name="group">
	                    	<option value="in">Cash In</option>
	                    	<option value="out">Cash Out</option>
	                    </select>
	                </div>
	               </div>

	               <div class="col-sm-4">
                      <div class="form-group">
                          <label>Cheque/Giro Date (optional)</label>
                          <div class="input-group date">
                            <div class="input-group-addon">
                              <i class="fa fa-calendar"></i>
                            </div>
                            <input type="text" id="invpayhGiro" name="trbank_girodate" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd">
                          </div>
                      </div>
                   </div>

                   <div class="col-sm-4">
                   		<div class="form-group">
		                    <label>No Giro / Check (optional)</label>
		                    <input type="text" name="trbank_girono" class="form-control">
		                </div>
                   </div>
	            </div>
                
                <div class="row">
                  <div class="col-sm-6">
                    <div class="form-group">
                        <label>Bank</label>
                        <select class="form-control cashbkId choose-style" name="cashbk_id" style="width:100%" required>
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
                        <select class="form-control paymtpCode choose-style" name="paymtp_id" style="width:100%" required>
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

                <div class="form-group">
                    <label>Description</label>
                    <input type="text" name="trbank_note" class="form-control">
                </div>
                <br><br>
                <hr>
                <h3 class="text-center">Transactions <span id="transtype">(Debit)</span></h3><br>
                <div class="row">
                	<div class="col-sm-3">
		              <div class="form-group">
		                <label>Description</label>
		                <input class="form-control" id="addDesc">
		              </div>
		            </div>

                	<div class="col-sm-3">
		              <div class="form-group">
		                <label>Amount</label>
		                <input class="form-control" id="addAmount">
		              </div>
		            </div>

		            <div class="col-sm-3">
		              <div class="form-group">
		                <label>Department</label>
		                <select name="dept_id" id="addDept" class="form-control">
		                	<option value="">Choose Department</option> 
		                	@foreach($departments as $dept)
		                	<option value="{{$dept->id}}">{{$dept->dept_name}}</option>
		                	@endforeach
		                </select>
		              </div>
		            </div>

		            <div class="col-sm-3">
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
                <thead>
                <tr>
                  <td>Account Code</td>
                  <td>Account Name</td>
                  <td>Dept</td>
                  <td>Description</td>
                  <td>Amount</td>
                  <td></td>
                </tr>
            	</thead>
                <tbody>
                <tr id="rowEmpty">
                  <td colspan="6"><center>Data Kosong. Pilih account dan Add Line terlebih dulu</center></td>
                </tr>
            	</tbody>
              </table>

              <br><br>
              <h4 class="pull-right">Total Amount : <b id="totalAmount">0</b></h4>
            </div>
          </div>
                
                <button type="submit" id="submitForm" class="btn btn-primary">submit</button>
            </form>
          </div>
        </div>
      </div>
    </div>
          <!-- content -->

    <!-- Modal extra -->
<div id="detailModal" class="modal fade" role="dialog">
    <div class="modal-dialog" style="width:900px">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Bankbook detail</h4>
            </div>
            <div class="modal-body" id="detailModalContent" style="padding: 20px 40px">
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th>Coa Code</th>
                      <th>Coa Name</th>
                      <th>Note</th>
                      <th>Dept</th>
                      <th>Debit</th>
                      <th>Credit</th>
                    </tr>
                  </thead>
                  <tbody id="tbody">
                  </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- End Modal -->
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
    var entity = "Bankbook"; // nama si tabel, ditampilin di dialog
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

    var coacode, coaname, desc, amount, deptid, deptname;
    $("#addAccount").click(function(){
      coacode = $('#selectAccount option:selected').val();
      if(coacode != ""){
        $('#rowEmpty').remove();
        coaname = $('#selectAccount option:selected').data('name');
        desc = $('#addDesc').val();
        amount = $('#addAmount').val();
        deptid = $('#addDept').val();
        deptname = $('#addDept option:selected').text();
        $('#tableJournal').append('<tr><input type="hidden" name="coa_code[]" value="'+coacode+'"><td>'+coacode+'</td><td>'+coaname+'</td><td><input type="hidden" name="dept_id[]" value="'+deptid+'">'+deptname+'</td><td><input type="hidden" name="description[]" value="'+desc+'">'+desc+'</td><td><input type="hidden" class="amount" name="amount[]" value="'+amount+'" >'+amount+'</td><td><a href="#" class="removeRow"><i class="fa fa-times text-danger"></i></a></td></tr>');
      	countTotal();
      }
 	});

 	function countTotal()
 	{
 		var total = 0;
 		$('#tableJournal tbody tr').each(function(){
 			var amount = parseFloat($(this).find('.amount').val());
 			console.log(amount);
 			total += amount;
 		});
 		$('#totalAmount').text(total);
 	}

 	$('select[name=group]').change(function(){
 		if($(this).val() == 'out') $('#transtype').text('(Credit)');
 		else $('#transtype').text('(Debit)');
 	});

 	$('#formPayment').submit(function(e){
        e.preventDefault();
        $('#submitForm').attr('disabled','disabled');

        if($('#tableJournal tbody tr .amount').length < 1){
        	alert('Please input debit/credit transactions');
        }else{
          var allFormData = $('#formPayment').serialize();
          var i;
          $.post('{{route('bankbook.insert')}}',allFormData, function(result){
              $('#submitForm').removeAttr('disabled');
              if(result.errorMsg) $.messager.alert('Warning',result.errorMsg);
	            if(result.success){ 
	              	$.messager.alert('Warning',result.message);
	              	location.reload();
	            }
          });

          return false;
        }
    });

	$(document).delegate('.removeRow','click',function(){
	      if(confirm('Are you sure want to remove this?')){
	          $(this).parent().parent().remove();
	          countTotal();
	      }
	 });

  // remove bankbook
  $(document).delegate('.remove','click',function(){
        if(confirm('Are you sure want to remove this?')){
            var id = $(this).data('id');
            $.post('{{route('bankbook.delete')}}', {id:id}, function(result){
                if(result.errorMsg) $.messager.alert('Warning',result.errorMsg);
                if(result.success){ 
                    $.messager.alert('Warning',result.message);
                    location.reload();
                }
            });
        }
   });

  $(document).delegate('.getDetail','click',function(){
      var id = $(this).data('id');
      $.post('{{route('bankbook.detail')}}', {id:id}, function(result){
          if(result.success) $('#tbody').html(result.data);
          else $('#tbody').html('<tr><td colspan="6">can not get detail</td></tr>');
      });
  });

	function postingInv(){
        var ids = [];
        $('input[name=check]:checked').each(function() {
           ids.push($(this).val());
        });
        if(ids.length > 0){
          $.messager.confirm('Confirm','Are you sure you want to post this '+ids.length+' Payment ?',function(r){
              if (r){
                  $('.loadingScreen').show();
                  $.post('{{route('bankbook.posting')}}',{id:ids}, function(data){
                      alert(data.message);
                      $('.loadingScreen').hide();
                      if(data.success == 1){
                        location.reload();
                      } else{
                        return false;
                      }
                  });
              }
          });
        }
    }
</script>
@endsection