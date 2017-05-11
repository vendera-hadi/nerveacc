@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Company
@endsection

<!-- page title -->
@section('contentheader_title')
  	Company Detail
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
        <li class="active">Company Detail</li>
    </ol>
@stop

@section('main-content')
<div class="container spark-screen">
		<div class="row">
			<div class="col-md-11">
          		<!-- content -->
          			<div class="row">
          				<div class="col-md-12">
          					<div class="box box-primary">
					            <div class="box-header with-border">
					              <h3 class="box-title">Company Detail Form</h3>
					            </div>
					            <!-- /.box-header -->
					            <!-- form start -->
					            
					            <form action="{{route('company.update')}}" method="post" enctype="multipart/form-data">
					              <div class="box-body">
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
						                  <label>Company Name</label>
						                  <input type="text" value="{{$company->comp_name}}" name="comp_name" class="form-control" id="compName" placeholder="Company Name" required>
						                </div>

						                <div class="form-group">
						                  <label>Company Address</label>
						                  <input type="text" value="{{$company->comp_address}}" name="comp_address" class="form-control" id="compAddress" placeholder="Company Address" required>
						                </div>

						                <div class="form-group">
						                  <label>Company Phone</label>
						                  <input type="text" value="{{$company->comp_phone}}" name="comp_phone" class="form-control" id="compPhone" placeholder="Company Phone" required>
						                </div>

						                <div class="form-group">
						                  <label>Company Fax</label>
						                  <input type="text" value="{{$company->comp_fax}}" name="comp_fax" class="form-control" id="compFax" placeholder="Company Fax">
						                </div>

						                <div class="form-group">
						                  <label>Invoice Signature Name</label>
						                  <input type="text" value="{{$company->comp_sign_inv_name}}" name="comp_sign_inv_name" class="form-control" id="compSignInvName" placeholder="Company Signature Name" required>
						                </div>

						                <div class="form-group">
						                  <label>Invoice Signature Postion</label>
						                  <input type="text" value="{{$company->comp_sign_position}}" name="comp_sign_position" class="form-control" id="compSignPosition" placeholder="Company Signature Postion" required>
						                </div>

						                <div class="form-group">
						                  <label>Building Insurance (IDR)</label>
						                  <input type="text" value="{{(int)$company->comp_build_insurance}}" name="comp_build_insurance" class="form-control numeric" id="compBuildInsurance" placeholder="Company Insurance" required>
						                </div>
						                <div class="form-group">
						                  <label>Cash Bank ID</label>
						                  <select name="cashbk_id" class="form-control" id="cashBank" required>
						                  	@foreach($cashbanks as $cb)
						                  	<option value="{{$cb->id}}" @if($cb->id == $company->cashbk_id){{'selected="selected"'}}@endif>
						                  		{{$cb->cashbk_name}}
						                  	</option>
						                  	@endforeach
						                  </select>
						                </div>

					                </div>
					                <div class="col-sm-6">
										<div class="form-group">
						                  <label>NPP Insurance (IDR)</label>
						                  <input type="text" value="{{(int)$company->comp_npp_insurance}}" name="comp_npp_insurance" class="form-control numeric" id="compNPPInsurance" placeholder="Company NPP Insurance" required>
						                </div>

						                <div class="form-group">
						                  <label>Materai 1</label>
						                  <input type="text" value="{{$company->comp_materai1}}" name="comp_materai1" class="form-control numeric" id="compMaterai1" placeholder="Company Materai 1" required>
						                </div>

						                <div class="form-group">
						                  <label>Materai 1 Amount (IDR)</label>
						                  <p class="help-block">Batasan maksimal transaksi</p>
						                  <input type="text" value="{{(int)$company->comp_materai1_amount}}" name="comp_materai1_amount" class="form-control numeric" id="compMateraiAmount1" placeholder="Company Materai Amount 1" required>
						                </div>

						                <div class="form-group">
						                  <label>Materai 2</label>
						                  <p class="help-block">Batasan dimulai dari batas maksimal Materai 1. Nol (0) untuk sampai tak terhingga </p>
						                  <input type="text" value="{{$company->comp_materai2}}" name="comp_materai2" class="form-control numeric" id="compMaterai2 numeric" placeholder="Company Materai 2" required>
						                </div>

						                <div class="form-group">
						                  <label>Materai 2 Amount (IDR)</label>
						                  <input type="text" value="{{(int)$company->comp_materai2_amount}}" name="comp_materai2_amount" class="form-control numeric" id="compMateraiAmount2" placeholder="Company Materai Amount 2" required>
						                </div>

						                @if($company->comp_image)
										<div class="form-group">
						                  <label>Current Image</label>
						                  <img src="{{asset('upload/'.$company->comp_image)}}" class="img-responsive">
						                </div>						                
						                @endif

						                <div class="form-group">
						                  <label>Company Image</label>
						                  <input type="file" name="image" id="companyImage">
						                  <p class="help-block">masukkan icon company anda</p>
						                </div>
					                
									</div>
					              </div>
					              <!-- /.box-body -->

					              <div class="box-footer">
					                <button type="submit" class="btn btn-primary">Submit</button>
					              </div>
					            </form>
					            

					          </div>
          				</div>
          			</div>
          		<!-- content -->
        	</div>
		</div>
	</div>
@endsection

@section('footer-scripts')
<script type="text/javascript">
$(document).ready(function() {
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