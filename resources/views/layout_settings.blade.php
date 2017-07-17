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
			<div class="col-md-12">
          		<!-- content -->
          		<div class="nav-tabs-custom">
			        <ul class="nav nav-tabs">
			            <li class="active"><a href="#tab_1" data-toggle="tab">Report Formats</a></li>
			            <li><a href="#tab_2" id="btn_to_tab2" class="edit_tab">Edit Format</a></li>
			        </ul>
          			<div class="tab-content">
            			<div class="tab-pane active" id="tab_1">

            			<div class="row">
							<div class="col-md-8">
								<a id="newFormat" class="btn btn-info">New Report Format</a><br><br>
			          		<!-- template tabel -->
			          		<table id="dg" title="Layouts" class="easyui-datagrid" style="width:100%;height:400px" toolbar="#toolbar">
			                    <!-- kolom -->
			                    <thead>
			                        <tr>
			                            <th field="name" width="40" sortable="true">Layout Name</th>
			                            <th field="type" width="40" sortable="true">Report Type</th>
			                        </tr>
			                    </thead>
			                </table>
			                <!-- end table -->

			                <div id="toolbar">
			                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editFormat()">Edit</a>
			                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editLayout()">Edit Layout</a>
			                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="destroyFormat()">Remove</a>
			                </div>

			            	</div>
			            </div>

		                </div>

		                <div class="tab-pane" id="tab_2" style="padding-bottom:80px">
		                	<form id="formDetail">
		                	<input type="hidden" name="id" value="">
		                	<div class="row">
		                		<div class="col-sm-12">
		                			<button id="addDetail" class="btn btn-info" style="margin-top:15px">Add Row</button>
		                		</div>
		                	</div>

		                	<div class="row">
		                		<div class="col-sm-12" style="overflow:auto">
		                			<h3 style="text-align: center;" class="lajurTitle">Lajur 1</h3>

		                			<table id="lajur1" class="table table-bordered" style="margin-top: 20px;">
		                				<thead>
		                					<tr>
			                                  <th>Account/Group</th>
			                                  <th>Description</th>
			                                  <th width="90px">Font Bold</th>
			                                  <th>Variable</th>
			                                  <th>Formula</th>
			                                  <th width="90px">Row Space</th>
			                                  <th width="90px">Underline</th>
			                                  <th width="90px">Hide</th>
			                                  <th>Action</th>
			                                </tr>
		                				</thead>
                            			<tbody>
			                                                        
                                        </tbody>
                           			</table>
                           		</div>
		                	</div>

		                	<div class="row" id="secondColumn">

		                		<div class="col-sm-12">
		                			<div class="row">
				                		<div class="col-sm-12">
				                			<button id="addDetail2" class="btn btn-info" style="margin-top:15px">Add Row</button>
				                		</div>
				                	</div>

		                			<h3 style="text-align: center;" class="lajurTitle">Lajur 2</h3>

		                			<table id="lajur2" class="table table-bordered" style="margin-top: 20px;">
		                				<thead>
		                					<tr>
			                                  <th>Account/Group</th>
			                                  <th>Description</th>
			                                  <th width="90px">Font Bold</th>
			                                  <th>Variable</th>
			                                  <th>Formula</th>
			                                  <th width="90px">Row Space</th>
			                                  <th width="90px">Underline</th>
			                                  <th width="90px">Hide</th>
			                                  <th>Action</th>
			                                </tr>
		                				</thead>
                            			<tbody>
			                                                        
                                        </tbody>
                           			</table>
                           		</div>
		                	</div>

		                	<div class="row">
		                		<div class="col-sm-12">
		                			<button class="btn btn-warning pull-right">Store Data</button>
		                		</div>
		                	</div>
		                	</form>
		                </div>
		            </div>
		        </div>
		    	
          		<!-- content -->
        	</div>
		</div>
	</div>

	<!-- Modal extra -->
<div id="formatModal" class="modal fade" role="dialog">
    <div class="modal-dialog" style="width:900px">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><span id="formatMode">Add New</span> Layout Format</h4>
            </div>
            <div class="modal-body" id="formatModalContent" style="padding: 20px 40px">
            	<form method="POST" id="formFormat">
            		<input type="hidden" name="id" value="">
                    <div class="row">
            			<div class="col-sm-6">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" class="form-control" name="name" placeholder="Name" required>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Format Type</label>
                                <select name="type" class="form-control">
                                	<option value="1">Staffel (1 Lajur)</option>
                                	<option value="2">Scontro (Bentuk T, 2 Lajur)</option>	
                                </select>
                            </div>
                        </div>

                        <!-- END OWNER -->
                        <div class="col-sm-12">
                            <button class="btn btn-info pull-right">Submit</button>
                            <button type="button" class="btn btn-danger pull-right" style="margin-right: 10px" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End Modal -->
@endsection

@section('footer-scripts')
<script src="{{asset('plugins/jQueryUI/jquery-ui.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('plugins/jquery-easyui/jquery.easyui.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/datagrid-filter.js') }}"></script>
<script type="text/javascript">
		var entity = "Layout"; // nama si tabel, ditampilin di dialog
        var get_url = "{{route('layout.get')}}";
        var lajur = 1;

        function editFormat(){
        	var row = $('#dg').datagrid('getSelected');
        	if(row){
	        	$('#formatModal').modal('show');
	        	$('#formatMode').text('Edit');
	        	$('#formFormat input[name=id]').val(row.id);
	        	$('#formFormat input[name=name]').val(row.name);
	        	$('#formFormat select[name=type]').val(row.typeid);
	        }
        }

        function editLayout(){
        	var row = $('#dg').datagrid('getSelected');
        	if(row){
        		$('.nav-tabs a[href="#tab_2"]').tab('show');
        		lajur = row.typeid;
        		if(row.typeid == 1) $('#secondColumn').hide();
        		else $('#secondColumn').show();
        		$('input[name=id]').val(row.id);
        		$.post('{{route("layout.detail.get")}}', {id:row.id}, function(result){
            		if(result.data1) parseResult($('#lajur1'), result.data1);
            		else emptyColumn($('#lajur1'));

            		if(result.data2) parseResult($('#lajur2'), result.data2);
            		else emptyColumn($('#lajur2')); 

            		if(result.errorMsg) $.messager.alert('Error',result.errorMsg);
            	}, 'json');
        	}
        }

        function destroyFormat(){
        	var row = $('#dg').datagrid('getSelected');
        	if(row){
	        	if(confirm('Are you sure want to delete '+row.name+' ?')){
	        		$.post('{{route("layout.delete")}}', {id: row.id}, function(result){
	            		if(result.success){ 
	            			$.messager.alert('Success',result.message);
	            			location.reload();
	            		}
	            		if(result.errorMsg) $.messager.alert('Error',result.errorMsg);
	            	}, 'json');
	        	}
	        }
        }

        var bold_option, underline_option, hide_option;
        function parseResult(targetClass, data){
            $.each(data, function(key, val) {
                if(val.header == "0") bold_option = '<option value="1">Yes</option><option value="0" selected>No</option>';
                else bold_option = '<option value="1">Yes</option><option value="0">No</option>';
                if(val.underline == false) underline_option = '<option value="1">Yes</option><option value="0" selected>No</option>';
                else underline_option = '<option value="1">Yes</option><option value="0">No</option>';
                if(val.hide == false) hide_option = '<option value="1">Yes</option><option value="0" selected>No</option>';
                else hide_option = '<option value="1">Yes</option><option value="0">No</option>';
                
                targetClass.find('tbody').append('<tr><td><input type="hidden" name="column[]" value="'+val.column+'"><input type="text" name="coa_code[]" class="form-control" placeholder="cth: 4, 401, @MODAL" value="'+val.coa_code+'" required></td><td><input type="text" name="desc[]" value="'+val.desc+'" class="form-control" required></td><td><select name="header[]" class="form-control">'+bold_option+'</select></td><td><input type="text" name="variable[]" value="'+val.variable+'" class="form-control" placeholder="cth : A1, B2, R99" required></td><td><input type="text" name="formula[]" value="'+val.formula+'" class="form-control" placeholder="cth: A1+A2+A3"></td><td><input type="number" name="linespace[]" value="'+val.linespace+'" class="form-control" value="0" placeholder="0" required></td><td><select name="underline[]" class="form-control">'+underline_option+'</select></td><td><select name="hide[]" class="form-control">'+hide_option+'</select></td><td><a class="removeDetail"><i class="fa fa-times"></i></a></td>');
            });
        }

        function emptyColumn(tableClass){
        	$(tableClass).find('tbody').html('<tr class="rowEmpty"><td colspan="9" class="text-center">Empty</td></tr>');
        }

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

            $('#newFormat').click(function(){
            	$('#formatModal').modal('show');
            	$('#formatMode').text('Add New');
            	$('#formFormat input[name=id]').val('');
            	$('#formFormat input[name=name]').val('');
            });

            $('input[name=neraca_space]').change(function(){
            	if($(this).val() < 0) $(this).val(0);
            	if($(this).val() > 5) $(this).val(5);
            });

            $('#formFormat').submit(function(e){
            	e.preventDefault();
            	$.post('{{route("layout.upsert")}}', $(this).serialize(), function(result){
            		if(result.success){ 
            			$.messager.alert('Success',result.message);
            			location.reload();
            		}
            		if(result.errorMsg) $.messager.alert('Error',result.errorMsg);
            	}, 'json');
            });

            $('#addDetail').click(function(e){
            	$('#lajur1').find('.rowEmpty').remove();
            	$('#lajur1').find('tbody').append('<tr><td><input type="hidden" name="column[]" value="1"><input type="text" name="coa_code[]" class="form-control" placeholder="cth: 4, 401, @MODAL" required></td><td><input type="text" name="desc[]" class="form-control" required></td><td><select name="header[]" class="form-control"><option value="1">Yes</option><option value="0">No</option></select></td><td><input type="text" name="variable[]" class="form-control" placeholder="cth : A1, B2, R99" required></td><td><input type="text" name="formula[]" class="form-control" placeholder="cth: A1+A2+A3"></td><td><input type="number" name="linespace[]" class="form-control" value="0" placeholder="0" required></td><td><select name="underline[]" class="form-control"><option value="1">Yes</option><option value="0" selected>No</option></select></td><td><select name="hide[]" class="form-control"><option value="1">Yes</option><option value="0" selected>No</option></select></td><td><a class="removeDetail"><i class="fa fa-times"></i></a></td>');
            });
			$('#addDetail2').click(function(e){
				$('#lajur2').find('.rowEmpty').remove();
            	$('#lajur2').find('tbody').append('<tr><td><input type="hidden" name="column[]" value="2"><input type="text" name="coa_code[]" class="form-control" placeholder="cth: 4, 401, @MODAL" required></td><td><input type="text" name="desc[]" class="form-control" required></td><td><select name="header[]" class="form-control"><option value="1">Yes</option><option value="0">No</option></select></td><td><input type="text" name="variable[]" class="form-control" placeholder="cth : A1, B2, R99" required></td><td><input type="text" name="formula[]" class="form-control" placeholder="cth: A1+A2+A3"></td><td><input type="number" name="linespace[]" class="form-control" value="0" placeholder="0" required></td><td><select name="underline[]" class="form-control"><option value="1">Yes</option><option value="0" selected>No</option></select></td><td><select name="hide[]" class="form-control"><option value="1">Yes</option><option value="0" selected>No</option></select></td><td><a class="removeDetail"><i class="fa fa-times"></i></a></td>');
            });

			$(document).delegate('.removeDetail','click',function(){
				$(this).parents('tr').remove();
			});

			$('#formDetail').submit(function(e){
				e.preventDefault();
				$.post('{{route("layout.detail.upsert")}}', $(this).serialize(), function(result){
					if(result.success){ 
                        $.messager.alert('Success',result.message);
                        location.reload();
                    }
            		if(result.errorMsg) $.messager.alert('Error',result.errorMsg);
            	}, 'json');	
			});
			
        });
</script>

</tr>
@endsection