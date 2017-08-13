@extends('layouts.app')

@section('htmlheader_title')
    Receive Money 
@endsection

@section('contentheader_title')
   Receive Money
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
        <li class="active">Terima Uang</li>
    </ol>
@stop

@section('main-content')
<div class="row">
    <div class="col-md-12">

        <!-- Tabs -->
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li><a href="{{route('bankbook.index')}}">Lists</a></li>
            <li><a href="{{route('bankbook.transfer')}}">Transfer Uang</a></li>
            <li  class="active"><a href="#">Terima Uang</a></li>
            <li><a href="{{route('bankbook.withdraw')}}">Kirim Uang</a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="tab_1" style="padding-top: 40px; padding-bottom: 5px;">
                @if(Session::get('error'))
                    <div class="alert alert-danger">
                      <strong>Error!</strong> {{ Session::get('error') }}
                    </div>
                @endif

                @if(Session::get('success'))
                    <div class="alert alert-success">
                      <strong>Success</strong> {{ Session::get('success') }}
                    </div>
                @endif

                @if (count($errors) > 0)
                  <div class="alert alert-danger">
                    <ul>
                      @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                      @endforeach
                    </ul>
                  </div>
                @endif

                <!-- form -->
                <form action="" method="POST">
                    <div class="row">

                    <div class="col-sm-4">
                      <div class="form-group">
                          <label>Kurs</label>
                          <select name="kurs_id" class="form-control" disabled="">
                              @foreach($kurs as $val)
                              <option value="{{$val->id}}" data-val="{{$val->value}}" @if($trbank->kurs_id == $val->id) selected @endif>{{$val->currency}}</option>
                              @endforeach
                          </select>
                      </div>
                     </div>


                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>No Voucher</label>
                                <input class="form-control" name="trbank_no" type="text" value="{{$trbank->trbank_no}}" required>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                              <label>Transaction Date</label>
                              <div class="input-group date">
                                <div class="input-group-addon">
                                  <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" id="invpayhDate" name="trbank_date" required="required" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd" value="{{date('Y-m-d',strtotime($trbank->trbank_date))}}">
                              </div>
                          </div>
                       </div>

                       
                    </div>
                
                    <div class="row">
                    <div class="col-sm-6">
                            <div class="form-group">
                                <label>Receiver</label>
                                <select class="form-control choose-style" name="cashbk_id" style="width:100%" required>
                                      <option value="">-</option>
                                      @foreach ($cashbank_data as $key => $value)
                                      <option value="<?php echo $value['id']?>" @if($trbank->cashbk_id == $value['id']) selected @endif><?php echo $value['cashbk_name']?></option>
                                      @endforeach
                                </select>
                            </div>
                       </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Note</label>
                                <textarea class="form-control" name="trbank_note">{{$trbank->trbank_note}}</textarea>
                            </div>
                        </div>
                   </div>

                    <!-- buat jurnal -->
                    <hr>
                    <div class="row">
                        <div class="col-sm-4">
                          <div class="form-group">
                            <label>Amount</label>
                            <input class="form-control" id="addAmount">
                          </div>
                        </div>                        

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>Type</label>
                                <select class="form-control" id="coatype">
                                  <option>DEBIT</option>
                                  <option>CREDIT</option>
                                </select>
                            </div>
                        </div>  

                        <div class="col-sm-4">
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

                        <div class="col-sm-12">
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

                <div class="row" style="margin-top:30px">
                    <div class="col-sm-12">
                      <table id="tableJournal" width="100%" class="table table-bordered">
                        <thead>
                        <tr>
                          <td>Type</td>
                          <td>Account Code</td>
                          <td>Account Name</td>
                          <td>Dept</td>
                          <td>Amount (IDR)</td>
                          <td></td>
                        </tr>
                        </thead>
                        <tbody>
                        @php $details = $trbank->detail->where('coa_code','!=', $trbank->coa_code); @endphp
                        @foreach($details as $detail)
                        <tr>
                          <td>@if($detail->debit > 0){!!'<input type="hidden" name="coa_type[]" value="DEBIT" class="type">DEBIT'!!}@else{!!'<input type="hidden" name="coa_type[]" value="CREDIT" class="type">CREDIT'!!}@endif</td>
                          <td><input type="hidden" name="coa_code[]" value="{{$detail->coa_code}}">{{$detail->coa_code}}</td><td>{{$detail->coa->coa_name}}</td>
                          <td><input type="hidden" name="dept_id[]" value="{{$detail->dept_id}}">{{$detail->dept->dept_name}}</td>
                          <td><input type="hidden" class="amount" name="amount[]" value="@if($detail->debit > 0){{(int)$detail->debit}}@else{{(int)$detail->credit}}@endif" >@if($detail->debit > 0){{(int)$detail->debit}}@else{{(int)$detail->credit}}@endif</td>
                          <td><a class="removeRow"><i class="fa fa-times text-danger"></i></a></td>
                        </tr>
                        @endforeach
                        </tbody>
                      </table>

                      <br><br>
                      <h4 class="pull-right">Total Amount : <b id="totalAmount">0</b></h4>
                    </div>
                </div>
                <!-- end -->

                <div class="row">
                    <div class="col-sm-12">
                        <button class="btn btn-info pull-right">Submit</button>
                    </div>
                </div>
                
                </form>
                <!-- form -->

                </div>
        </div>
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
$(function(){
    $('.datepicker').datepicker({
        autoclose: true
    });

    $(".js-example-basic-single").select2();
    countTotal();
});

var coacode, coaname, desc, amount, deptid, deptname, type, kursval;
$("#addAccount").click(function(){
  coacode = $('#selectAccount option:selected').val();
  if(coacode != ""){
    $('#rowEmpty').remove();
    coaname = $('#selectAccount option:selected').data('name');
    type = $('#coatype').val();
    desc = $('#addDesc').val();
    kursval = $('select[name=kurs_id] option:selected').data('val');
    amount = $('#addAmount').val() * kursval;
    deptid = $('#addDept').val();
    deptname = $('#addDept option:selected').text();
    $('#tableJournal').append('<tr><td><input type="hidden" name="coa_type[]" value="'+type+'" class="type">'+type+'</td><td><input type="hidden" name="coa_code[]" value="'+coacode+'">'+coacode+'</td><td>'+coaname+'</td><td><input type="hidden" name="dept_id[]" value="'+deptid+'">'+deptname+'</td><td><input type="hidden" class="amount" name="amount[]" value="'+amount+'" >'+amount+'</td><td><a class="removeRow"><i class="fa fa-times text-danger"></i></a></td></tr>');
    countTotal();
  }
});

function countTotal()
{
    var total = 0;
    if($('#tableJournal tbody tr').length > 0){
        $('#tableJournal tbody tr').each(function(){
            var amount = parseFloat($(this).find('.amount').val());
            var type = $(this).find('.type').val();
            if(type == 'CREDIT') total += amount;
            else total -= amount;
        });
    }else{
        $('#tableJournal tbody').append('<tr id="rowEmpty"><td colspan="6"><center>Data Kosong. Pilih account dan Add Line terlebih dulu</center></td></tr>');
    }
    $('#totalAmount').text(total);
}

$(document).delegate('.removeRow','click',function(){
      if(confirm('Are you sure want to remove this?')){
          $(this).parent().parent().remove();
          countTotal();
      }
 });
</script>
@endsection