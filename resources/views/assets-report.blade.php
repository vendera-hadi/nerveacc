@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Report All Assets
@endsection

<!-- page title -->
@section('contentheader_title')
   Report All Assets
@endsection

<!-- tambahan script atas -->
@section('htmlheader_scripts')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/default/easyui.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/icon.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/color.css') }}">

    <!-- datepicker -->
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/datepicker/datepicker3.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/select2/select2.min.css') }}">
@endsection

@section('contentheader_breadcrumbs')
  <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Report All Assets</li>
    </ol>
@stop

@section('main-content')
<div class="row">
    <div class="col-md-12">
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li><a href="{{route('fixed_asset.index')}}">List Harta</a></li>
                <li><a href="{{route('fixed_asset.type.index')}}">Kelompok Harta</a></li>
                <li class="active"><a href="#tab_3" data-toggle="tab">Report</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab_3">
                    <?php if(isset($report_isi) && count($report_isi) > 0) { ?>
                    <div class="row">
                    	<div class="col-md-3">
                    		<button type="button" class="btn btn-flat btn-primary btn-block" id="excel">Excel</button>
                    	</div>
                    	<div class="col-md-3">
                    		<button type="button" class="btn btn-flat btn-info btn-block" id="pdf" style="display: none;">Pdf</button>
                    	</div>
                    	<div class="col-md-3">
                    		<button type="button" class="btn btn-flat btn-default btn-block" id="print" style="display: none;">Print</button>
                    	</div>
                    	<div class="col-md-3">
                    		<iframe id="frame" style="display: none;"></iframe>
                    	</div>
                    </div>
                    <br>
                    <div class="table-responsive">
	                    <table class="table table-bordered">
	                    	<tr>
	                    		<th style="text-align: center;">JENIS HARTA</th>
	                    		<th style="text-align: center;">KELOMPOK HARTA</th>
	                    		<th style="text-align: center;">MASA MANFAAT</th>
	                    	</tr>
	                    	<?php for($i=0; $i<count($report_isi); $i++){ ?>
	                    		<tr>
	                    			<td><?php echo $report_isi[$i]['jenis_harta'] ?></td>
	                    			<td><?php echo $report_isi[$i]['kelompok_harta'] ?></td>
	                    			<td><?php echo $report_isi[$i]['masa_manfaat'].' TAHUN' ?></td>
	                    		</tr>
	                    		<?php if(count($report_isi[$i]['detail']) > 0){ ?>
	                    		<tr>
	                    			<td colspan="3">
	                    				<table class="table table-bordered" style="margin-bottom: 0px;">
	                    					<tr>
	                    						<td>No.</td>
	                    						<td>Name</td>
	                    						<td>Depresiasi</td>
	                    						<td>Tanggal</td>
	                    						<td>Harga</td>
	                    						<td>Supplier</td>
	                    						<td>No.PO</td>
	                    						<td>No.Aktiva</td>
	                    						<td>Cabang</td>
	                    						<td>Lokasi</td>
	                    						<td>Area</td>
	                    						<td>Dept</td>
	                    						<td>User</td>
	                    						<td>Kondisi</td>
	                    						<td>Keterangan</td>
	                    					</tr>
	                    					<?php for($k=0; $k<count($report_isi[$i]['detail']); $k++){ ?>
	                    					<tr>
	                    						<td><?php echo ($k+1) ?></td>
	                    						<td><?php echo $report_isi[$i]['detail'][$k]['name']; ?></td>
	                    						<td><?php echo $report_isi[$i]['detail'][$k]['depreciation_type']; ?></td>
	                    						<td><?php echo $report_isi[$i]['detail'][$k]['date']; ?></td>
	                    						<td><?php echo number_format($report_isi[$i]['detail'][$k]['price']); ?></td>
	                    						<td><?php echo $report_isi[$i]['detail'][$k]['supplier']; ?></td>
	                    						<td><?php echo $report_isi[$i]['detail'][$k]['po_no']; ?></td>
	                    						<td><?php echo $report_isi[$i]['detail'][$k]['kode_induk']; ?></td>
	                    						<td><?php echo $report_isi[$i]['detail'][$k]['cabang']; ?></td>
	                    						<td><?php echo $report_isi[$i]['detail'][$k]['lokasi']; ?></td>
	                    						<td><?php echo $report_isi[$i]['detail'][$k]['area']; ?></td>
	                    						<td><?php echo $report_isi[$i]['detail'][$k]['departemen']; ?></td>
	                    						<td><?php echo $report_isi[$i]['detail'][$k]['user']; ?></td>
	                    						<td><?php echo $report_isi[$i]['detail'][$k]['kondisi']; ?></td>
	                    						<td><?php echo $report_isi[$i]['detail'][$k]['keterangan']; ?></td>
	                    					</tr>
	                    					<?php } ?>
	                    				</table>
	                    			</td>
	                    		</tr>
	                    		<?php } ?>
	                    	<?php } ?>
	                    </table>
	                </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
        
</div>
@endsection

@section('footer-scripts')
<script type="text/javascript">
	var report_url = '{!! url('/') !!}/fixed-asset-report';

	$('#pdf').click(function(){
        
    });

    $('#excel').click(function(){
        $('#frame').attr('src', report_url+'?excel=1');
    });

    $('#print').click(function(){
        var url = current_url+'&print=1';
        var title = 'PRINT REPORT';
        var w = 640;
        var h = 660;

        openWindow(url, title, w, h);
        return false;
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
</script>
@endsection