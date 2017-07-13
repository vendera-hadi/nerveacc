@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Report Layouts 
@endsection

<!-- page title -->
@section('contentheader_title')
   Report Layouts
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
        <li class="active">Report Layouts</li>
    </ol>
@stop

@section('main-content')
	<div class="container spark-screen">
		<div class="row">
			<div class="col-md-11">
          		<!-- content -->

          		<div class="nav-tabs-custom">
			        <ul class="nav nav-tabs">
			            <li class="active"><a href="#tab_1" data-toggle="tab">Report Formats</a></li>
			            <li><a href="#tab_2" class="edit_tab" data-toggle="tab">Edit Format</a></li>
			        </ul>
          			<div class="tab-content">
            			<div class="tab-pane active" id="tab_1">

            			<div class="row">
							<div class="col-md-6">
			          		<!-- template tabel -->
			          		<table id="dg" title="Layouts" class="easyui-datagrid" style="width:100%;height:400px" toolbar="#toolbar">
			                    <!-- kolom -->
			                    <thead>
			                        <tr>
			                            <th field="kodefmt" width="50" sortable="true">Layout Name</th>
			                            <th field="action" width="10" sortable="true">Action</th>
			                        </tr>
			                    </thead>
			                </table>
			                <!-- end table -->
			            	</div>
			            </div>

		                </div>

		                <div class="tab-pane" id="tab_2">
		                	<!-- next tab -->
		                	<div class="row">
		                		<div class="col-sm-2">
		                			<div class="form-group">
		                              	<label>Acc / Group Acc</label>
		                              	<input type="text" name="coa_code" required="required" class="form-control">
		                          	</div>
		                		</div>

		                		<div class="col-sm-2">
		                			<div class="form-group">
		                              	<label>Description</label>
		                              	<input type="text" name="neraca_desc" required="required" class="form-control">
		                          	</div>
		                		</div>

		                		<div class="col-sm-2">
		                			<div class="form-group">
		                              	<label>Font Bold</label>
		                              	<input type="text" name="neraca_font" required="required" class="form-control">
		                          	</div>
		                		</div>

		                		<div class="col-sm-2">
		                			<div class="form-group">
		                              	<label>Row (Variable)</label>
		                              	<input type="text" name="neraca_variable" required="required" class="form-control">
		                          	</div>
		                		</div>

		                		<div class="col-sm-2">
		                			<div class="form-group">
		                              	<label>Formula</label>
		                              	<input type="text" name="neraca_formula" required="required" class="form-control">
		                          	</div>
		                		</div>
		                	</div>

		                	<div class="row">
		                		<div class="col-sm-2">
		                			<div class="form-group">
		                              	<label>Row Space</label>
		                              	<input type="text" name="neraca_space" required="required" class="form-control">
		                          	</div>
		                		</div>

		                		<div class="col-sm-2">
		                			<div class="form-group">
		                              	<label>Underline</label>
		                              	<input type="text" name="neraca_line" required="required" class="form-control">
		                          	</div>
		                		</div>

		                		<div class="col-sm-2">
		                			<div class="form-group">
		                              	<label>Hide Item</label>
		                              	<input type="text" name="neraca_line" required="required" class="form-control">
		                          	</div>
		                		</div>

		                		<div class="col-sm-2 col-sm-offset-2">
		                			<button class="btn btn-success pull-right" style="margin-top:15px">Submit</button>
		                		</div>
		                	</div>

		                	<div class="row">
		                		<div class="col-sm-12">
		                			<table class="table table-bordered">
		                				<thead>
		                					<tr>
			                                  <th>Account/Group</th>
			                                  <th>Description</th>
			                                  <th>Font Bold</th>
			                                  <th>Variable</th>
			                                  <th>Formula</th>
			                                  <th>Row Space</th>
			                                  <th>Underline</th>
			                                  <th>Hide</th>
			                                  <th style="width: 40px">Action</th>
			                                </tr>
		                				</thead>
                            			<tbody>
			                                                        
                                        </tbody>
                           			</table>
                           		</div>
		                	</div>

		                </div>
		            </div>
		        </div>

          		<!-- content -->
        	</div>
		</div>
	</div>
@endsection

@section('footer-scripts')
<script src="{{asset('plugins/jQueryUI/jquery-ui.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('plugins/jquery-easyui/jquery.easyui.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/datagrid-filter.js') }}"></script>
<script type="text/javascript">
		var entity = "Layout"; // nama si tabel, ditampilin di dialog
        var get_url = "{{route('layout.get')}}";

		$(function(){
            var dg = $('#dg').datagrid({
                url: get_url,
                pagination: true,
                remoteFilter: true, //utk jalanin search filter
                rownumbers: true,
                singleSelect: true,
                fitColumns: true,
                pageSize:100,
                pageList: [100,500,1000],
            });
            dg.datagrid('enableFilter');
        });
</script>
@endsection