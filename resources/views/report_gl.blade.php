@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    General Ledger Report
@endsection

<!-- page title -->
@section('contentheader_title')
   General Ledger Report
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
        <li class="active">GL Report</li>
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
                              <select name="dept" class="form-control">
                                <option value="">All Department</option> 
                                @foreach($departments as $dept)
                                <option value="{{$dept->id}}" @if(Request::input('dept') == $dept->id){{'selected="selected"'}}@endif>{{$dept->dept_name}}</option>
                                @endforeach
                              </select>
                            </div>

                            <div class="col-sm-3">
                                <select class="form-control" name="jour_type_id">
                                  <option value="">All Journal Type</option>
                                  @foreach($journal_types as $jourtype)
                                  <option value="{{$jourtype->id}}" @if(Request::input('jour_type_id') == $jourtype->id){{'selected="selected"'}}@endif>{{$jourtype->jour_type_name}}</option>
                                  @endforeach
                                </select>
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
                            
                            <div class="col-sm-3">
                                <button class="btn btn-info">Submit</button>
                            </div>
                        </div>

                        <div class="row" style="margin-bottom:15px">
                            <div class="col-sm-3">
                                <select name="coa" class="form-control js-example-basic-single" id="selectAccount" style="width:100%">
                                      <option value="">Choose COA</option>                                     
                                      @foreach($accounts as $key => $coa)
                                          <option value="{{$coa->coa_code}}" data-name="{{$coa->coa_name}}">{{$coa->coa_code." ".$coa->coa_name}}</option>
                                      @endforeach
                                </select>
                            </div>

                            <div class="col-sm-3">
                                <input class="form-control" type="text" name="q" placeholder="Keyword (Tenant Name or Description)">
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
<!-- select2 -->
<script type="text/javascript" src="{{ asset('plugins/select2/select2.min.js') }}"></script>
<script type="text/javascript">
    $('.datepicker').datepicker({
            autoclose: true
        });

    var current_url;
    $('#filter').submit(function(e){
        e.preventDefault();
        var report_url = '{!! route('report.glget') !!}';
        var from = $('input[name=from]').val();
        var to = $('input[name=to]').val(); 
        
        var queryString = $(this).serialize();
        current_url = report_url+'?'+queryString;
        $('#frame').attr('src', current_url);
        $('#pdf').show();
    });

    $('#pdf').click(function(){
        $('#frame').attr('src', current_url+'&pdf=1');
    });

</script>
@endsection