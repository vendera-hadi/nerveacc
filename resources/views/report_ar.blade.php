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
                                    <option value="outinv">Outstanding By Invoice</option>
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
                        </form>

                        <div class ="row" style="margin-top:80px">
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <button id="pdf" class="btn btn-success pull-right" style="margin-bottom:15px; display:none">Download Pdf</button>
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
        if(type == 'araging'){
            var jenis = $('#ty').val();
            var ag30 = $('input[name=ag30]').val();
            var ag60 = $('input[name=ag60]').val();
            var ag90 = $('input[name=ag90]').val();
            var ag180 = $('input[name=ag180]').val();

            current_url = report_url+type+'?ty='+jenis+'&ag30='+ag30+'&ag60='+ag60+'&ag90='+ag90+'&ag180='+ag180;
        }else{
            current_url = report_url+type+'?from='+from+'&to='+to;
        }
        $('#frame').attr('src', current_url);
        $('#pdf').show();
    });

    $('#pdf').click(function(){
        $('#frame').attr('src', current_url+'&pdf=1');
    });

    $('#type').on('change', function() {
      var hasil = this.value;
      if(hasil != "araging"){
        $( ".history" ).hide();
        $( ".dates" ).show();
      }else{
        $( ".dates" ).hide();
        $( ".history" ).show();
      }
    });
</script>
@endsection