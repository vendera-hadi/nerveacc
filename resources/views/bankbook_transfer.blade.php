@extends('layouts.app')

@section('htmlheader_title')
    Transfer Money 
@endsection

@section('contentheader_title')
   Transfer Money
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
        <li class="active">Transfer Uang</li>
    </ol>
@stop

@section('main-content')
<div class="row">
    <div class="col-md-12">

        <!-- Tabs -->
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li><a href="{{route('bankbook.index')}}">Lists</a></li>
            <li class="active"><a href="#">Transfer Uang</a></li>
            <li><a href="{{route('bankbook.deposit')}}">Terima Uang</a></li>
            <li><a href="{{route('bankbook.withdraw')}}">Kirim Uang</a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="tab_1" style="padding-top: 40px; padding-bottom: 60px;">
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
                <form action="{{route('bankbook.dotransfer')}}" method="POST">
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
                            <label>Transfer from</label>
                            <select class="form-control choose-style" name="from_coa" style="width:100%" required>
                                  <option value="">-</option>
                                  @foreach ($cashbank_data as $key => $value)
                                  <option value="<?php echo $value['coa_code']?>"><?php echo $value['cashbk_name']?></option>
                                  @endforeach
                            </select>
                        </div>
                   </div>
                   <div class="col-sm-6">
                        <div class="form-group">
                            <label>Transfer to</label>
                            <select class="form-control choose-style" name="to_coa_id" style="width:100%" required>
                                  <option value="">-</option>
                                  @foreach ($cashbank_data as $key => $value)
                                  <option value="<?php echo $value['id']?>"><?php echo $value['cashbk_name']?></option>
                                  @endforeach
                            </select>
                        </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-sm-6">
                        <div class="form-group">
                            <label>Amount (Rp.)</label>
                            <input class="form-control" type="number" name="amount" value="0" required>
                        </div>
                   </div>
                   <div class="col-sm-6">
                      <div class="form-group">
                          <label>Note</label>
                          <textarea class="form-control" name="trbank_note"></textarea>
                      </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <button class="btn btn-flat btn-info pull-right">Submit</button>
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
});
</script>
@endsection