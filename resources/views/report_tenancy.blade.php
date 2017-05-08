@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Tenancy Reports
@endsection

<!-- page title -->
@section('contentheader_title')
   Tenancy Reports
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
        <li class="active">Reports Tenancy</li>
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
                            <div class="col-sm-6">
                                <select id="type" name="type" class="form-control">
                                    <option value="r_meter">Report Reading Meter</option>
                                    <option value="r_unit">Report Unit</option>
                                    <option value="r_tenant">Report Tenant</option>
                                </select>
                            </div>
                            <div class="col-sm-2 history">
                                <div class="form-group">
                                  <select class="form-control" name="tahun" id="th">
                                    <?php 
                                        for($i=date('Y'); $i>=2015; $i--){
                                            echo '<option value="'.$i.'">'.$i.'</option>';
                                        }
                                    ?>
                                  </select>
                                </div>
                            </div>
                            <div class="col-sm-2 history">
                                <div class="form-group">
                                  <select class="form-control" name="jenis" id="ty">
                                    <option value="1">Electric</option>
                                    <option value="2">Water</option>
                                  </select>
                                </div>
                            </div>
                            <div class="col-sm-1">
                                <button class="btn btn-info">Submit</button>
                            </div>
                        </div>
                        </form>

                        <div class ="row" style="margin-top:80px">
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <button id="pdf" class="btn btn-success" style="margin-bottom:15px; display:none">Download Pdf</button>
                                <button id="excel" class="btn btn-primary" style="margin-bottom:15px; display:none;">Download Excel</button>
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
        var year =$( "#th option:selected" ).val();
        var cost =$( "#ty option:selected" ).val();
        current_url = report_url+type+'?year='+year+'&cost='+cost;
        $('#frame').attr('src', current_url);
        if(type == "r_tenant"){
            $('#pdf').hide();
            $('#excel').show();
        }else if(type == "r_unit"){
            $('#excel').show();
            $('#pdf').show();
        }else{
            $('#excel').hide();
            $('#pdf').show();
        }
    });

    $('#pdf').click(function(){
        $('#frame').attr('src', current_url+'&pdf=1');
    });

    $('#excel').click(function(){
        $('#frame').attr('src', current_url+'&excel=1');
    });

    $('#type').on('change', function() {
      var hasil = this.value;
      if(hasil != "r_meter"){
        $( ".history" ).hide();
      }else{
        $( ".history" ).show();
      }
    })
</script>
@endsection