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
					            
					            <form role="form">
					              <div class="box-body">
					              	<div class="col-sm-6">
					                
						                <div class="form-group">
						                  <label>Company Name</label>
						                  <input type="text" name="comp_name" class="form-control" id="compName" placeholder="Company Name">
						                </div>

						                <div class="form-group">
						                  <label>Company Address</label>
						                  <input type="text" name="comp_address" class="form-control" id="compAddress" placeholder="Company Address">
						                </div>

						                <div class="form-group">
						                  <label>Company Phone</label>
						                  <input type="text" name="comp_phone" class="form-control" id="compPhone" placeholder="Company Phone">
						                </div>

						                <div class="form-group">
						                  <label>Company Fax</label>
						                  <input type="text" name="comp_fax" class="form-control" id="compFax" placeholder="Company Fax">
						                </div>

						                <div class="form-group">
						                  <label>Invoice Signature Name</label>
						                  <input type="text" name="comp_sign_inv_name" class="form-control" id="compSignInvName" placeholder="Company Signature Name">
						                </div>

						                <div class="form-group">
						                  <label>Building Insurance (IDR)</label>
						                  <input type="text" name="comp_build_insurance" class="form-control" id="compBuildInsurance" placeholder="Company Insurance">
						                </div>

						                <div class="form-group">
						                  <label>NPP Insurance (IDR)</label>
						                  <input type="text" name="comp_npp_insurance" class="form-control" id="compNPPInsurance" placeholder="Company NPP Insurance">
						                </div>

						                <div class="form-group">
						                  <label>Materai 1</label>
						                  <input type="text" name="comp_materai1" class="form-control" id="compMaterai1" placeholder="Company Materai 1">
						                </div>

						                <div class="form-group">
						                  <label>Materai 1 Amount (IDR)</label>
						                  <input type="text" name="comp_materai1_amount" class="form-control" id="compMateraiAmount1" placeholder="Company Materai Amount 1">
						                </div>

						                <div class="form-group">
						                  <label>Materai 2</label>
						                  <input type="text" name="comp_materai2" class="form-control" id="compMaterai2" placeholder="Company Materai 2">
						                </div>

						                <div class="form-group">
						                  <label>Materai 2 Amount (IDR)</label>
						                  <input type="text" name="comp_materai2_amount" class="form-control" id="compMateraiAmount2" placeholder="Company Materai Amount 2">
						                </div>

						                <div class="form-group">
						                  <label>Cash Bank ID</label>
						                  <select name="cashbk_id" class="form-control" id="cashBank">
						                  </select>
						                </div>

						                <div class="form-group">
						                  <label>Company Image</label>
						                  <input type="file" id="companyImage">
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

@endsection