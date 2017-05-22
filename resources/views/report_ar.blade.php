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
    <!-- select2 -->
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/select2/select2.min.css') }}">
    <style type="text/css">
    .select2-container--default .select2-selection--single{
      border-radius: 0px;
      height: 36px;
      width: 235px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow{
        top: 3px;
        right: -130px;
    }
    .select2-container--open .select2-dropdown--below{
        width: 235px !important;
    }
    </style>
@endsection

@section('contentheader_breadcrumbs')
	<ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">AR Reports</li>
    </ol>
@stop

@section('main-content')
	<div class="container spark-screen">
		<div class="row">
			<div class="col-md-11">
          		<!-- content -->
                <div class="box" style="min-height:500px">
                    <div class="box-body">
                        <form action="post" id="filter">
                        <div class ="row">
                            <div class="col-sm-3">
                                <select id="type" name="type" class="form-control">
                                    <option value="arbyinvoice">Invoice</option>
                                    <option value="arbyinvoicecancel">Invoice Cancel</option>
                                    <option value="araging">Aging Invoices</option>
                                    <option value="outinv">Outstanding By Unit</option>
                                    <option value="outcontr">Outstanding By Billing Info</option>
                                    <option value="payment">Payment History</option>
                                </select>
                            </div>
                            <div class="col-sm-2 history" style="display: none;">
                                <select class="form-control" name="jenis" id="ty">
                                    <option value="1">NOT PAID</option>
                                    <option value="2">PAID</option>
                                  </select>
                            </div>
                            <div class="col-sm-1 history" style="display: none;">
                                <div class="form-group">
                                 <input type="text" name="ag30" class="form-control" value="30" />
                                </div>
                            </div>
                            <div class="col-sm-1 history" style="display: none;">
                                <div class="form-group">
                                 <input type="text" name="ag60" class="form-control" value="60" />
                                </div>
                            </div>
                            <div class="col-sm-1 history" style="display: none;">
                                <div class="form-group">
                                 <input type="text" name="ag90" class="form-control" value="90" />
                                </div>
                            </div>
                            <div class="col-sm-1 history" style="display: none;">
                                <div class="form-group">
                                 <input type="text" name="ag180" class="form-control" value="180" />
                                </div>
                            </div>
                            
                            <div class="col-sm-3 dates">
                                <div class="form-group">
                                    <div class="input-group date">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input type="text" class="form-control datepicker" name="from" placeholder="Date From" data-date-format="yyyy-mm-dd">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3 dates">
                                <div class="form-group">
                                    <div class="input-group date">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input type="text" class="form-control datepicker" name="to" placeholder="Date To" data-date-format="yyyy-mm-dd">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <button class="btn btn-info">Submit</button>
                            </div>
                        </div>

                        <div class="row unit" style="display:none">
                            <div class="col-sm-3">
                                <div class="form-group">
                                 <select class="form-control choose-unit" name="unit"></select>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                 <select class="form-control" name="inv_type">
                                    @foreach($invtypes as $invtype)
                                    <option value="{{$invtype->id}}">{{$invtype->invtp_name}}</option>
                                    @endforeach
                                 </select>
                                </div>
                            </div>
                        </div>

                        <div class="row payment" style="display:none">
                            <div class="col-sm-3">
                                <div class="form-group">
                                 <select class="form-control choose-unit" name="unit2"></select>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                 <input type="text" name="inv_number" class="form-control" placeholder="No Invoice"/>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group">
                                 <select class="form-control" name="bank_id">
                                    <option value="">Choose Bank</option>
                                    @foreach($banks as $bank)
                                    <option value="{{$bank->id}}">{{$bank->cashbk_name}}</option>
                                    @endforeach
                                 </select>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group">
                                 <select class="form-control" name="payment_id">
                                    <option value="">Choose Payment Type</option>
                                    @foreach($payment_types as $paym)
                                    <option value="{{$paym->id}}">{{$paym->paymtp_name}}</option>
                                    @endforeach
                                 </select>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group">
                                 <select class="form-control" name="post_status">
                                    <option value="">Choose Post Status</option>
                                    <option value="1">POSTED</option>
                                    <option value="2">NOT POSTED</option>
                                </select>
                                </div>
                            </div>
                        </div>

                        </form>

                        <div class ="row" style="margin-top:80px">
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <button id="pdf" class="btn btn-success" style="margin-bottom:15px; display:none">Pdf</button>
                                <button id="excel" class="btn btn-info" style="margin-bottom:15px; display:none">Excel</button>
                                <button id="print" class="btn btn-primary" style="margin-bottom:15px; display:none">Print</button>
                                <iframe id="frame" style="width:100%; border: 1px solid #f1ebeb; height:500px"></iframe>
                            </div>
                        </div>
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
        // if(type == 'araging'){
        //     var jenis = $('#ty').val();
        //     var ag30 = $('input[name=ag30]').val();
        //     var ag60 = $('input[name=ag60]').val();
        //     var ag90 = $('input[name=ag90]').val();
        //     var ag180 = $('input[name=ag180]').val();

        //     current_url = report_url+type+'?ty='+jenis+'&ag30='+ag30+'&ag60='+ag60+'&ag90='+ag90+'&ag180='+ag180;
        // }else{
        //     current_url = report_url+type+'?from='+from+'&to='+to;
        // }
        $('#frame').attr('src', current_url);
        $('#pdf').show();
        $('#excel').show();
        $('#print').show();
    });

    $('#pdf').click(function(){
        $('#frame').attr('src', current_url+'&pdf=1');
    });

    $('#type').on('change', function() {
      var hasil = this.value;
      if(hasil == "araging"){
        $( ".dates,.unit,.payment" ).hide();
        $( ".history" ).show();
      }else if(hasil == 'outinv'){
        $( ".unit,.dates" ).show();
        $( ".history,.payment" ).hide();
      }else if(hasil == 'payment'){
        $( ".dates,.payment" ).show();
        $( ".history,.unit" ).hide();
      }else{
        $( ".history,.unit,.payment" ).hide();
        $( ".dates" ).show();
      }
    });

    $(".choose-unit").select2({
          placeholder: "Select an Unit",
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