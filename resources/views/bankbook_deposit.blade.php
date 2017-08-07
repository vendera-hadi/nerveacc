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
                <form action="{{route('bankbook.dodeposit')}}" method="POST">
                  <div class="row">
                    <div class="col-sm-6">
                      <div class="form-group">
                        <label>No Voucher</label>
                        <input class="form-control" name="trbank_no" type="text" required>
                      </div>
                    </div>

                    <div class="col-sm-6">
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
                  </div>
                  <div class="row">
                    <div class="col-sm-6">
                      <div class="form-group">
                        <label>Receiver</label>
                        <select class="form-control choose-style" name="cashbk_id" style="width:100%" required>
                          <option value="">-</option>
                          @foreach ($cashbank_data as $key => $value)
                          <option value="<?php echo $value['id']?>"><?php echo $value['cashbk_name']?></option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" name="trbank_note"></textarea>
                        </div>
                    </div> 
                  </div>
                    <!-- buat jurnal -->
                    <hr>
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
    $('#tableJournal').append('<tr><input type="hidden" name="coa_code[]" value="'+coacode+'"><td>'+coacode+'</td><td>'+coaname+'</td><td><input type="hidden" name="dept_id[]" value="'+deptid+'">'+deptname+'</td><td><input type="hidden" name="description[]" value="'+desc+'">'+desc+'</td><td><input type="hidden" class="amount" name="amount[]" value="'+amount+'" >'+amount+'</td><td><a class="removeRow"><i class="fa fa-times text-danger"></i></a></td></tr>');
    countTotal();
  }
});

function countTotal()
{
    var total = 0;
    if($('#tableJournal tbody tr').length > 0){
        $('#tableJournal tbody tr').each(function(){
            var amount = parseFloat($(this).find('.amount').val());
            console.log(amount);
            total += amount;
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