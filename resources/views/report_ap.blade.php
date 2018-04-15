@extends('layouts.app')
<!-- title tab -->
@section('htmlheader_title')
    Account Payable Reports
@endsection

<!-- page title -->
@section('contentheader_title')
   Account Payable Reports
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
        <li class="active">AP Reports</li>
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
                        <option value="apaging">Aged Payable</option>
                        <option value="polist">PO List</option>
                        <option value="nonpolist">Non PO List</option>
                        <option value="phistory">AP Payment History</option>
                    </select>
                </div>
                <div class="row history">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <select class="form-control choose-unit" name="unit3" style="width: 100%;"></select>
                        </div>
                        <div class="form-group phis">
                            <select class="form-control" name="jenist" id="tyt">
                                <option value="2">SUMMARY</option>
                                <option value="1">DETAIL</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group phis">
                            <select class="form-control" name="jenis" id="ty">
                                <option value="1">NOT PAID</option>
                                <option value="2">PAID</option>
                                <option value="3">ALL</option>
                            </select>
                        </div>
                        <div class="age">
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
                                 <input type="text" name="ag180" class="form-control" value="180" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- datepicker -->
                    <div class="col-md-6 paymhistory" style="display: none">
                        <div class="form-group">
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" class="form-control datepicker" name="from" placeholder="Date From" data-date-format="yyyy-mm-dd">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 paymhistory" style="display: none">
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
                    <!--<button id="excel" class="btn btn-info" style="margin-bottom:15px; display:none">Excel</button>!-->
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

    $(".choose-unit").select2({
          placeholder: "Select an Supplier",
          allowClear: true,
          ajax: {
            url: "{{route('supplier.select2')}}",
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

    $('#type').on('change', function() {
      var hasil = this.value;
      if(hasil == "apaging"){
        $( ".history" ).show();
        $( ".age" ).show();
        $( ".phis" ).show();
        $(".paymhistory").hide();
      }else if(hasil == "phistory"){
        $( ".history" ).show();
        $( ".age" ).hide();
        $( ".phis" ).hide();
        $(".paymhistory").show();
      }else{
        $( ".history" ).show();
        $( ".age" ).hide();
        $( ".phis" ).show();
        $(".paymhistory").hide();
      }
    });
</script>
@endsection