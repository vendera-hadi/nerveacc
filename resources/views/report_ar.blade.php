@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Account Receivables Reports
@endsection

<!-- page title -->
@section('contentheader_title')
   Account Receivables Reports
@endsection

<!-- tambahan script atas -->
@section('htmlheader_scripts')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/default/easyui.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/icon.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/color.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/datepicker/datepicker3.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/select2/select2.min.css') }}">

@endsection

@section('contentheader_breadcrumbs')
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">AR Reports</li>
    </ol>
@stop

@section('main-content')
<div class="row">
    <!-- left column -->
    <div class="col-md-12">
      <!-- general form elements -->
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">Filter Report</h3>
        </div>
        <!-- /.box-header -->
        <!-- form start -->
        <form role="form" action="post" id="filter">
            <div class="box-body">
                <div class="form-group">
                    <select id="type" name="type" class="form-control">
                        <option value="arbyinvoice">Invoice Issued Summary</option>
                        <option value="arbyinvoicecancel">List Canceled Invoice</option>
                        <option value="araging">Aged Receivable IPL</option>
                        <option value="araging2">Aged Receivable Total</option>
                        <option value="payment">Payment History</option>
                        <option value="outinv">Outstanding By Unit</option>
                        <option value="outcontr">Outstanding By Billing Info</option>
                        <option value="arsummary">AR Summary By Unit</option>
                        <option value="vasummary">VA By Invoice Type</option>
                        <option value="sms">SMS Blast Template</option>
                        <option value="wablast">WA Blast Template</option>
                        <option value="depositsummary">Deposit</option>
                        <option value="manualsummary">Manual Invoice</option>
                        <option value="dendasummary">Denda</option>
                        <option value="spsummary">Reminder Manual</option>
                        <option value="vaother">VA Other</option>
                        <option value="lebihbayar">Status Lebih Bayar</option>
                        <option value="lebihpembayaran">Lebih Pembayaran Unit</option>
                        <option value="piutangpenghuni">Pemotongan Piutang Penghuni</option>
                    </select>
                </div>
                <div class="row dates">
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" class="form-control datepicker" name="from" placeholder="Date From" data-date-format="yyyy-mm-dd">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" class="form-control datepicker" name="to" placeholder="Date To" data-date-format="yyyy-mm-dd">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row unit" style="display:none">
                    <div class="col-sm-6">
                        <div class="form-group">
                         <select class="form-control choose-unit" name="unit" style="width: 100%;"></select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                         <select class="form-control" name="inv_type">
                            @foreach($invtypes as $invtype)
                            <option value="{{$invtype->id}}">{{$invtype->invtp_name}}</option>
                            @endforeach
                         </select>
                        </div>
                    </div>
                </div>
                <div class="row unit2" style="display:none">
                    <div class="col-sm-12">
                        <div class="form-group">
                         <select class="form-control choose-unit" name="unit5" style="width: 100%;"></select>
                        </div>
                    </div>
                </div>
                <div class="row payment" style="display:none">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <select class="form-control choose-unit" name="unit2" style="width: 100%;"></select>
                        </div>
                        <div class="form-group">
                            <select class="form-control" name="bank_id">
                                <option value="">Choose Bank</option>
                                @foreach($banks as $bank)
                                <option value="{{$bank->id}}">{{$bank->cashbk_name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <select class="form-control" name="post_status">
                                <option value="">Choose Post Status</option>
                                <option value="1">POSTED</option>
                                <option value="2">NOT POSTED</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <input type="text" name="inv_number" class="form-control" placeholder="No Invoice"/>
                        </div>
                        <div class="form-group">
                            <select class="form-control" name="payment_id">
                                <option value="">Choose Payment Type</option>
                                @foreach($payment_types as $paym)
                                <option value="{{$paym->id}}">{{$paym->paymtp_name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <select class="form-control" name="date_type">
                                <option value="0">Payment Date</option>
                                <option value="1">Posting at</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row history" style="display:none">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <select class="form-control choose-unit" name="unit3" style="width: 100%;"></select>
                        </div>
                        <div class="form-group">
                            <select class="form-control" name="jenist" id="tyt">
                                <option value="1">SUMMARY</option>
                                <option value="2">DETAIL</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="col-sm-3" style="padding-left: 0px;">
                            <div class="form-group">
                             <input type="text" name="ag30" class="form-control" value="30" />
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                             <input type="text" name="ag60" class="form-control" value="60" />
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                             <input type="text" name="ag90" class="form-control" value="90" />
                            </div>
                        </div>
                        <div class="col-sm-3" style="padding-right: 0px;">
                            <div class="form-group">
                             <input type="text" name="ag180" class="form-control" value="90" />
                            </div>
                        </div>
                        <div class="col-sm-6" style="padding-left:0;">
                            <div class="form-group">
                                <select class="form-control" name="jenis" id="ty">
                                    <option value="1">NOT PAID</option>
                                    <option value="2">PAID</option>
                                    <option value="3">ALL</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6" style="padding-right:0;">
                            <div class="form-group">
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                    <input type="text" class="form-control datepicker" name="cutoff" placeholder="Date Cut Off" data-date-format="yyyy-mm-dd" value="<?php echo date('Y-m-d') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row vasummary" style="display:none">
                    <div class="col-md-4">
                        <div class="form-group">
                            <select class="form-control" name="bulan" id="bln">
                                <option value="1">JANUARY</option>
                                <option value="2">FEBRUARY</option>
                                <option value="3">MARCH</option>
                                <option value="4">APRIL</option>
                                <option value="5">MAY</option>
                                <option value="6">JUNE</option>
                                <option value="7">JULY</option>
                                <option value="8">AUGUST</option>
                                <option value="9">SEPTEMBER</option>
                                <option value="10">OCTOBER</option>
                                <option value="11">NOVEMBER</option>
                                <option value="12">DECEMBER</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <select class="form-control" name="tahun" id="thn">
                                <?php 
                                    for($i=2018; $i<=date('Y'); $i++){
                                        echo '<option value="'.$i.'">'.$i.'</option>';
                                    }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 smsview">
                        <div class="form-group">
                            <div class="form-group">
                                <select class="form-control" name="inv_type_2">
                                    @foreach($invtypes as $invtype)
                                    <option value="{{$invtype->id}}">{{$invtype->invtp_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 rekap" style="display: none;">
                        <div class="form-group">
                            <div class="form-group">
                                <select class="form-control" name="rekap">
                                    <option value="1">REKAP</option>
                                    <option value="0">PERBULAN</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row spsummary" style="display:none">
                    <div class="col-md-12">
                        <div class="form-group">
                            <select class="form-control" name="sptype" id="sp">
                                <option value="4">SP1</option>
                                <option value="5">SP2</option>
                                <option value="6">SP3</option>
                                <option value="0">ALL</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row unit-bayar" style="display:none">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <select class="form-control choose-unit" name="unit4" style="width: 100%;"></select>
                        </div>
                    </div>
                </div>

            </div>
          <!-- /.box-body -->

          <div class="box-footer">
            <button type="submit" class="btn btn-flat btn-primary">Submit</button>
          </div>
        </form>
      </div>
      <!-- /.box -->
    </div>
</div>

<div class="row">
    <!-- left column -->
    <div class="col-md-12">
      <!-- general form elements -->
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">Result</h3>
        </div>
        <!-- /.box-header -->
          <div class="box-body">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <button id="pdf" class="btn btn-success" style="margin-bottom:15px; display:none">Pdf</button>
                    <button id="excel" class="btn btn-info" style="margin-bottom:15px; display:none">Excel</button>
                    <button id="print" class="btn btn-primary" style="margin-bottom:15px; display:none">Print</button>
                    <iframe id="frame" style="width:100%; border: 1px solid #f1ebeb; height:400px"></iframe>
                </div>
            </div>
          </div>
          <!-- /.box-body -->
      </div>
      <!-- /.box -->
    </div>
</div>
@endsection

@section('footer-scripts')
<script src="{{asset('plugins/jQueryUI/jquery-ui.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('plugins/jquery-easyui/jquery.easyui.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/datagrid-filter.js') }}"></script>
<!-- datepicker -->
<script type="text/javascript" src="{{ asset('plugins/datepicker/bootstrap-datepicker.js') }}"></script>
<!-- select2 -->
<script type="text/javascript" src="{{ asset('plugins/select2/select2.min.js') }}"></script>
<script type="text/javascript">
    $('.datepicker').datepicker({
            autoclose: true
        });

    var current_url;
    $('#filter').submit(function(e){
        e.preventDefault();
        var type = $('#type').val();
        var report_url = '{!! url('/') !!}/report/';
        var from = $('input[name=from]').val();
        var to = $('input[name=to]').val();

        var queryString = $(this).serialize();
        current_url = report_url+type+'?'+queryString;
        $('#frame').attr('src', current_url);
        $('#pdf').show();
        $('#excel').show();
        $('#print').show();
    });

    $('#pdf').click(function(){
        $('#frame').attr('src', current_url+'&pdf=1');
    });

    $('#excel').click(function(){
        $('#frame').attr('src', current_url+'&excel=1');
    });

    function openWindow(url, title, w, h){
        // Fixes dual-screen position                         Most browsers      Firefox
        var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
        var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;

        var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
        var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

        var left = ((width / 2) - (w / 2)) + dualScreenLeft;
        var top = ((height / 2) - (h / 2)) + dualScreenTop;
        var newWindow = window.open(url, title, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

        // Puts focus on the newWindow
        if (window.focus) {
            newWindow.focus();
        }
    }

    $('#print').click(function(){
        var url = current_url+'&print=1';
        var title = 'PRINT REPORT';
        var w = 640;
        var h = 660;

        openWindow(url, title, w, h);
        return false;
    });

    $('#type').on('change', function() {
      var hasil = this.value;
      if(hasil == "araging" || hasil == "araging2"){
        $( ".dates,.unit,.payment,.unit2,.vasummary,.spsummary,.unit-bayar" ).hide();
        $( ".history" ).show();
      }else if(hasil == 'outinv'){
        $( ".unit,.dates" ).show();
        $( ".history,.payment,.unit2,.vasummary,.spsummary,.unit-bayar" ).hide();
      }else if(hasil == 'payment'){
        $( ".dates,.payment" ).show();
        $( ".history,.unit,.unit2,.vasummary,.spsummary,.unit-bayar" ).hide();
      }else if(hasil == 'arsummary'){
        $( ".dates,.history,.payment,.unit,.vasummary,.spsummary,.unit-bayar" ).hide();
        $( ".unit2" ).show();
      }else if(hasil == 'spsummary'){
        $( ".spsummary" ).show();
        $( ".dates,.history,.unit,.payment,.unit2,.vasummary" ).hide();
      }else if(hasil == 'vasummary' || hasil == 'sms' || hasil == 'depositsummary' || hasil == 'manualsummary' || hasil == 'dendasummary' || hasil == 'vaother' || hasil == 'wablast'){
        $( ".dates,.history,.payment,.unit,.unit2,.spsummary,.unit-bayar" ).hide();
        $( ".vasummary" ).show();
        if(hasil == 'depositsummary' || hasil == 'vaother'){
            $( ".rekap" ).show();
        }
        if(hasil == 'sms' || hasil == 'depositsummary' || hasil == 'manualsummary' || hasil == 'dendasummary' || hasil == 'vaother' || hasil == 'wablast'){
            $( ".smsview" ).hide();
            if(hasil == 'depositsummary' || hasil == 'vaother'){
                $( ".rekap" ).show();
            }
        }else{
            $( ".smsview" ).show();
            $( ".rekap" ).hide();
        }
      }else if(hasil == 'lebihbayar'){
        $( ".history,.unit,.payment,.unit2,.vasummary,.spsummary,.dates,.unit-bayar" ).hide();
      }else if(hasil == 'lebihpembayaran'){
        $( ".dates,.unit-bayar" ).show();
        $( ".history,.unit,.unit2,.vasummary,.spsummary,.payment" ).hide();
      }else if(hasil == 'piutangpenghuni'){
        $( ".dates,.unit-bayar" ).show();
        $( ".history,.unit,.unit2,.vasummary,.spsummary,.payment" ).hide();
      }else{
        $( ".history,.unit,.payment,.unit2,.vasummary,.spsummary,.unit-bayar" ).hide();
        $( ".dates" ).show();
      }
    });

    $(".choose-unit").select2({
          placeholder: "Select an Unit",
          allowClear: true,
          ajax: {
            url: "{{route('unit.select2')}}",
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
</script>
@endsection