@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    General Ledger Report
@endsection

<!-- page title -->
@section('contentheader_title')
   YTD General Ledger Report
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
    </style>
@endsection

@section('contentheader_breadcrumbs')
	<ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">YTD GL Report</li>
    </ol>
@stop

@section('main-content')
		<div class="row">
			<div class="col-md-12">
          		<!-- content -->
                <div class="box" style="min-height:500px">
                    <div class="box-body">
                        <form action="post" id="filter">
                        <div class ="row">
                            <div class="col-sm-2">
                              <select name="monthfrom" class="form-control" required>
                                <option value="">Start Month</option> 
                                @for($i=1; $i<=12; $i++)
                                <option value="{{$i}}" @if(Request::input('monthfrom') == $i){{'selected="selected"'}}@endif>{{date('M',strtotime(date('Y').'-'.$i.'-01'))}}</option>
                                @endfor
                              </select>
                            </div>

                            <div class="col-sm-2">
                                <select name="yearfrom" class="form-control" required>
                                <option value="">Start Month</option> 
                                @for($i=2016; $i<=date('Y'); $i++)
                                <option value="{{$i}}" @if(Request::input('yearfrom') == $i){{'selected="selected"'}}@endif>{{$i}}</option>
                                @endfor
                              </select>
                            </div>

                            <div class="col-sm-1">
                                <center>to</center>
                            </div>
                            
                            <div class="col-sm-2">
                                <select name="monthto" class="form-control" required>
                                <option value="">End Month</option> 
                                @for($i=1; $i<=12; $i++)
                                <option value="{{$i}}" @if(Request::input('monthto') == $i){{'selected="selected"'}}@endif>{{date('M',strtotime(date('Y').'-'.$i.'-01'))}}</option>
                                @endfor
                              </select>
                            </div>
                            
                             <div class="col-sm-2">
                                <select name="yearto" class="form-control" required>
                                <option value="">Start Month</option> 
                                @for($i=2016; $i<=date('Y'); $i++)
                                <option value="{{$i}}" @if(Request::input('yearfrom') == $i){{'selected="selected"'}}@endif>{{$i}}</option>
                                @endfor
                              </select>
                            </div>

                            <div class="col-sm-2">
                                <button class="btn btn-info">Submit</button>
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
        var report_url = '{!! route('report.ytdget') !!}';
        var queryString = $(this).serialize();
        current_url = report_url+'?'+queryString;
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

</script>
@endsection