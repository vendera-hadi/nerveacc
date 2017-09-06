@extends('layouts.app')

@section('htmlheader_title')
    Bank Reconcile
@endsection

@section('contentheader_title')
   Bank Reconcile
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
        <li class="active">Rekonsiliasi Bank</li>
    </ol>
@stop

@section('main-content')
<div class="row">
        <form action="" method="GET">
        <div class="col-sm-3">
            <div class="form-group">
                <label>Bank</label>
                <select class="form-control cashbkId choose-style" name="cashbk_id" style="width:100%" required>
                    <option value="">-</option>
                    @foreach ($cashbank_data as $key => $value)
                    <option value="{{$value['id']}}" @if(Request::get('cashbk_id') == $value['id']) selected @endif>{{$value['cashbk_name']}}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-sm-3">
            <div class="form-group">
                <label>Start date</label>
                <div class="input-group date">
                    <div class="input-group-addon">
                      <i class="fa fa-calendar"></i>
                    </div>
                    <input type="text" id="invpayhDate" name="start" required="required" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd" value="{{Request::get('start')}}" required>
                </div>
            </div>
        </div>

        <div class="col-sm-3">
            <div class="form-group">
                <label>End date</label>
                <div class="input-group date">
                    <div class="input-group-addon">
                      <i class="fa fa-calendar"></i>
                    </div>
                    <input type="text" id="invpayhDate" name="end" required="required" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd" value="{{Request::get('end')}}" required>
                </div>
            </div>
        </div>

        <div class="col-sm-3">
            <button class="btn btn-flat btn-info btn-block" style="margin-top: 23px;">Search</button>
        </div>
        </form>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="box box-primary">
            <div class="box-body">
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

                <form action="{{route('reconcile.update')}}" method="POST">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Voucher No</th>
                            <th>Receive</th>
                            <th>Sent</th>
                            <th>Reconcile Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($trbanks) > 0)
                        @foreach($trbanks as $trbank)
                        <tr>
                            <td>{{date('d/m/Y', strtotime($trbank->trbank_date))}}</td>
                            <td>{{$trbank->trbank_no}}</td>
                            <td>{{'Rp.'.number_format($trbank->trbank_in,2)}}</td>
                            <td>{{'Rp.'.number_format($trbank->trbank_out,2)}}</td>
                            <td>
                                <input type="hidden" name="id[]" value="{{$trbank->id}}">
                                <select class="form-control select-rekon" name="rekon[]">
                                    <option value="1">Ya</option>
                                    <option value="0" @if(!$trbank->trbank_rekon) selected @endif>Belum</option>
                                </select>
                            </td>
                        </tr>
                        @endforeach
                        @else
                        <tr>
                            <td colspan="5"><center>No result</center></td>
                        </tr>
                        @endif
                    </tbody>
                </table>
                @if(count($trbanks) > 0)
                <button class="btn btn-info pull-right" style="margin-top:20px">Save Reconcile Status</button>
                <button type="button" class="btn btn-warning pull-right" style="margin-top:20px; margin-right: 10px" id="yesall">Pilih Ya Semua</button>
                <button type="button" class="btn btn-warning pull-right" style="margin-top:20px; margin-right: 10px" id="noall">Pilih Belum Semua</button>
                @endif
                </form>
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
    var entity = "Reconcile"; // nama si tabel, ditampilin di dialog
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

        $('#yesall').click(function(){
            $('.select-rekon').each(function(){
                $(this).val("1");
            });
        });

        $('#noall').click(function(){
            $('.select-rekon').each(function(){
                $(this).val("0");
            });
        })
    });

    $('.datepicker').datepicker({
        autoclose: true
    });
</script>
@endsection