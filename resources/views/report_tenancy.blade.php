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
                        <option value="r_meter">Report Reading Meter</option>
                        <option value="r_unit">Report Unit</option>
                        <option value="r_tenant">Report Tenant</option>
                    </select>
                </div>
                <div class="row history">
                    <div class="col-sm-6">
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
                    <div class="col-sm-6">
                        <div class="form-group">
                            <select class="form-control" name="jenis" id="ty">
                                <option value="1">Electric</option>
                                <option value="2">Water</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
          <!-- /.box-body -->

          <div class="box-footer">
            <button type="submit" class="btn btn-primary">Submit</button>
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
      if(hasil != "r_meter"){
        $( ".history" ).hide();
      }else{
        $( ".history" ).show();
      }
    })
</script>
@endsection