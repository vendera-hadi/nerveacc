@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Close Entries
@endsection

<!-- page title -->
@section('contentheader_title')
  	Close Entries
@endsection

<!-- tambahan script atas -->
@section('htmlheader_scripts')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/default/easyui.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/icon.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/color.css') }}">
@endsection

@section('contentheader_breadcrumbs')
	<ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Close Entries</li>
    </ol>
@stop

@section('main-content')
<div class="row">
	<div class="col-md-12">
		<!-- content -->
		<div class="row">
          	<div class="col-md-12">
          		<div class="box box-primary">
		            <div class="box-header with-border">
		              <h3 class="box-title">Close Entries</h3>
		            </div>
					<form action="" method="post" id="formClosing">
					    <div class="box-body">
					    	<div class="row">
						        <div class="col-sm-6">
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
					                <div class="form-group">
										<label>Jenis Closing</label>
										<select name="closing_type" class="form-control" id="closingType" required>
											<option value="">Choose Type</option>
											<option value="monthly">BULANAN</option>
											<option value="yearly">TAHUNAN</option>
										</select>
					                </div>
					                <div class="form-group" id="monthField">
										<label>Bulan</label>
										<select name="month" class="form-control" id="month">
											<option value="01">JANUARI</option>
											<option value="02">FEBRUARI</option>
											<option value="03">MARET</option>
											<option value="04">APRIL</option>
											<option value="05">MEI</option>
											<option value="06">JUNI</option>
											<option value="07">JULI</option>
											<option value="08">AGUSTUS</option>
											<option value="09">SEPTEMBER</option>
											<option value="10">OKTOBER</option>
											<option value="11">NOVEMBER</option>
											<option value="12">DESEMBER</option>
										</select>
					                </div>
									<div class="form-group" id="yearField">
										<label>Tahun</label>
										<select name="year" class="form-control" id="year">
											@for($i=2016;$i<=date('Y');$i++)
											<option value="{{$i}}" @if($i == date('Y')){{'selected'}}@endif>{{$i}}</option>
											@endfor
										</select>
									</div>
						    	</div>
						    </div>
						</div>
						<div class="box-footer">
							<button type="submit" class="btn btn-flat btn-primary" id="submit">Submit</button>
						</div>
					</form>
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
<script type="text/javascript" src="{{ asset('js/datagrid-detailview.js') }}"></script>
<script type="text/javascript">
$(document).ready(function() {
	$('#yearField,#monthField').hide();

	$('#closingType').change(function(){
		var value = $(this).val();
		if(value == 'monthly'){
			$('#yearField,#monthField').show();	
		}else if(value == 'yearly'){
			$('#yearField').show();
			$('#monthField').hide();
		}else{
			$('#yearField,#monthField').hide();
		}
	});

	$('#formClosing').submit(function(e){
		e.preventDefault();
		if($('#closingType').val() == ''){
			$.messager.alert('Warning','Pilih Closing Type Terlebih dahulu');
		}else{
			var url = '{{route('clentry.update')}}';
			if(confirm('Apakah anda yakin ingin melakukan Closing sekarang? (Setelah Closing data entry tidak dapat diedit atau dihapus lagi)')){
		        $('#submit').attr('disabled','disabled').text('Loading ....');
		        $.post(url, $(this).serialize(), function(data){
		        	$('#submit').removeAttr('disabled').text('Submit');
		            if(data.errorMsg) $.messager.alert('Warning',data.errorMsg);
		            if(data.success){
		            	$.messager.alert('Success',data.success);
		            	//location.reload();
		            }
		        });
		    }
		}
	});

    $(".numeric").keydown(function (e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
             // Allow: Ctrl+A, Command+A
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
             // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
                 // let it happen, don't do anything
                 return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
});
</script>
@endsection