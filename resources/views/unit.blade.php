@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Unit
@endsection

<!-- page title -->
@section('contentheader_title')
   Master Unit
@endsection

<!-- tambahan script atas -->
@section('htmlheader_scripts')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/default/easyui.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/icon.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/color.css') }}">
    <!-- datepicker -->
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/datepicker/datepicker3.css') }}">
@endsection

@section('contentheader_breadcrumbs')
	<ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Master Unit</li>
    </ol>
@stop

@section('main-content')
	<div class="container spark-screen">
		<div class="row">
			<div class="col-md-11">
          		<!-- content -->

                <!-- template tabel -->
          		<table id="dg" title="Master Unit" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
                    <!-- kolom -->
                    <thead>
                        <tr>
                            <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                            <th field="unit_code" width="120" sortable="true">Unit Code</th>
                            <th field="tenan_name" width="120" sortable="true">Owner</th>
                            <!-- <th field="unit_name" width="120" sortable="true">Unit Name</th> -->
                            <th field="unit_sqrt" width="120" sortable="true">Luas</th>
                            <th field="unit_virtual_accn" width="120" sortable="true">Virtual Account</th>
                            <th field="untype_name" width="120" sortable="true">Unit Type</th>
                            <th field="floor_name" width="120" sortable="true">Unit Floor</th>
                            <th field="unit_isactive" width="120" sortable="true">Unit Active</th>
                            <th field="created_by" width="120" sortable="true">Created By</th>
                        </tr>
                    </thead>
                </table>
                <!-- end table -->
                
                <!-- icon2 atas table -->
                <div id="toolbar">
                    @if(Session::get('role')==1 || in_array(16,Session::get('permissions')))
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="createNew()">New</a>
                    @endif
                    @if(Session::get('role')==1 || in_array(17,Session::get('permissions')))
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editUnit()">Edit</a>
                    @endif
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-search" plain="true" onclick="detail()">View Detail</a>
                    @if(Session::get('role')==1 || in_array(18,Session::get('permissions')))
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="destroyUser()">Remove</a>
                    @endif
                </div>
                <!-- end icon -->
            
                <!-- hidden form buat create edit -->
                <div id="dlg" class="easyui-dialog" style="width:60%"
                        closed="true" buttons="#dlg-buttons">
                    <form id="fm" method="post" novalidate style="margin:0;padding:20px 50px">
                        <div style="margin-bottom:20px;font-size:14px;border-bottom:1px solid #ccc">Input Data</div>
                        <div style="margin-bottom:10px">
                            <input name="unit_code" class="easyui-textbox" label="Unit Code:" style="width:100%" data-options="required:true,validType:'length[0,15]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="unit_name" class="easyui-textbox" label="Unit Name:" style="width:100%" data-options="required:true,validType:'length[0,25]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="unit_sqrt" class="easyui-textbox" label="Unit Square:" style="width:100%" data-options="required:true">
                        </div>
                        <div style="margin-bottom:10px">
                            <input id="cc" class="easyui-combobox" required="true" name="unit_virtual_accn" style="width:100%" label="Unit Virtual Account:" data-options="valueField:'id',textField:'text',url:'{{route('unit.option2')}}'">
                        </div> 
                        <div style="margin-bottom:10px">
                            <input id="cc" class="easyui-combobox" required="true" name="floor_id" style="width:100%" label="Unit Floor:" data-options="valueField:'id',textField:'text',url:'{{route('unit.fopt')}}'">
                        </div> 
                        <div style="margin-bottom:10px">
                            <input id="cc" class="easyui-combobox" required="true" name="untype_id" style="width:100%" label="Unit Type:" data-options="valueField:'id',textField:'text',url:'{{route('unit.options')}}'">
                        </div>
                        <div style="margin-bottom:10px">
                            <select id="cc" class="easyui-combobox" required="true" name="unit_isactive" label="Active:" style="width:300px;">
                                <option value="true">yes</option>
                                <option value="false">no</option>
                            </select>
                        </div>  
                    </form>
                </div>
                <div id="dlg-buttons">
                    <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveUser()" style="width:90px">Save</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg').dialog('close')" style="width:90px">Cancel</a>
                </div>
                <!-- end form -->

          		<!-- content -->
        	</div>
		</div>
	</div>

    <!-- Modal extra -->
    <div id="addUnitModal" class="modal fade" role="dialog">
      <div class="modal-dialog" style="width:900px">

        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Add New Unit</h4>
          </div>
          <div class="modal-body" id="addUnitModalContent" style="padding: 20px 40px">
                <!-- isi form -->
                <form method="POST" id="formAddUnit">
                    <div class="row">
                        <div class="col-sm-12 text-center" style="margin-bottom: 20px;">
                            <h4><strong>Unit Details</strong></h4>
                        </div>
                        <!-- UNIT -->
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Unit Code</label>
                                <input type="text" class="form-control" name="unit_code" placeholder="Unit Code" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Unit Square</label>
                                <input type="text" class="form-control" name="unit_sqrt" placeholder="Unit Area (m2)" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Virtual Account</label>
                                <input type="text" class="form-control" name="virtual_account" placeholder="Virtual Account">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>No. Meter Air</label>
                                <input type="text" class="form-control" name="meter_air" placeholder="No. Meter Air">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>No. Meter Listrik</label>
                                <input type="text" class="form-control" name="meter_listrik" placeholder="No. Meter Listrik">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Floor</label>
                                <select name="floor_id" class="form-control" required>
                                @foreach($floors as $floor)
                                    <option value="{{$floor->id}}">{{$floor->floor_name}}</option>
                                @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Unit Type</label>
                                <select name="untype_id" class="form-control" required>
                                @foreach($unittypes as $unittype)
                                    <option value="{{$unittype->id}}">{{$unittype->untype_name}}</option>
                                @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- END UNIT -->
                        <div class="col-sm-12 text-center" style="margin-bottom: 20px;">
                            <h4><strong>Owner Details</strong></h4>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Pilih Owner</label><br>
                                <input type="radio" name="owner" value="0" checked> &nbsp; Owner Baru &nbsp;&nbsp;&nbsp;
                                <input type="radio" name="owner" value="1"> &nbsp; Owner dari Tenan Terdaftar
                            </div>
                        </div>
                        <!-- OWNER -->
                        <div id="oldowner" class="hidden">
                            <div class="col-xs-6">
                                <div class="form-group">
                                    <label>Tenant</label>
                                    <div class="input-group">
                                      <input type="hidden" name="tenan_id" id="txtTenanId" required>
                                      <input type="text" class="form-control" id="txtTenan" disabled>
                                      <span class="input-group-btn">
                                        <button class="btn btn-info" type="button" id="chooseTenanButton">Choose Tenant</button>
                                      </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="newowner">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" class="form-control" name="tenan_name" placeholder="Owner Name" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>KTP</label>
                                    <input type="text" class="form-control" name="tenan_idno" placeholder="Owner KTP" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Phone</label>
                                    <input type="text" class="form-control" name="tenan_phone" placeholder="Owner Phone Number" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>FAX</label>
                                    <input type="text" class="form-control" name="tenan_fax" placeholder="Owner Fax Number">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="text" class="form-control" name="tenan_email" placeholder="Owner Email Address" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Address</label>
                                    <input type="text" class="form-control" name="tenan_address" placeholder="Owner Address">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>NPWP</label>
                                    <input type="text" class="form-control" name="tenan_npwp" placeholder="Owner NPWP Number">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Tax Name (Nama NPWP)</label>
                                    <input type="text" class="form-control" name="tenan_taxname" placeholder="Owner NPWP Name">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Tax Address (Alamat NPWP)</label>
                                    <input type="text" class="form-control" name="tenan_tax_address" placeholder="Owner NPWP Address">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>PPN</label>
                                    &nbsp;&nbsp;<input type="checkbox" name="tenan_isppn" value="1" >
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>PKP</label>
                                    &nbsp;&nbsp;<input type="checkbox" name="tenan_ispkp" value="1" > 
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Own Unit Since</label>
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                    <input type="text" class="form-control datepicker" name="unitow_start_date" placeholder="Own Unit Since" data-date-format="yyyy-mm-dd" required>
                                </div>
                            </div>
                        </div>
                        <!-- END OWNER -->
                        <div class="col-sm-12">
                            <button class="btn btn-info pull-right">Submit</button>
                            <button type="button" class="btn btn-danger pull-right" style="margin-right: 10px" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </form>
                <!-- end form -->
          </div>
        </div>

      </div>
    </div>

    <!-- End Modal -->

    <!-- Modal extra -->
    <div id="editUnitModal" class="modal fade" role="dialog">
      <div class="modal-dialog" style="width:900px">

        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Edit Unit</h4>
          </div>
          <div class="modal-body" id="editUnitModalContent" style="padding: 20px 40px">
                <!-- isi form -->
                <form method="POST" id="formEditUnit">
                    <div class="row">
                        <div class="col-sm-12 text-center" style="margin-bottom: 20px;">
                            <h4><strong>Unit Details</strong></h4>
                            <input type="hidden" name="id" class="unit_id">
                        </div>
                        <!-- UNIT -->
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Unit Code</label>
                                <input type="text" class="form-control unit_code" name="unit_code" placeholder="Unit Code" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Unit Square</label>
                                <input type="text" class="form-control unit_sqrt" name="unit_sqrt" placeholder="Unit Area (m2)" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Virtual Account</label>
                                <input type="text" class="form-control virtual_account" name="virtual_account" placeholder="Virtual Account">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>No. Meter Air</label>
                                <input type="text" class="form-control meter_air" name="meter_air" placeholder="No. Meter Air">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>No. Meter Listrik</label>
                                <input type="text" class="form-control meter_listrik" name="meter_listrik" placeholder="No. Meter Listrik">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Floor</label>
                                <select name="floor_id" class="form-control floor_id" required>
                                @foreach($floors as $floor)
                                    <option value="{{$floor->id}}">{{$floor->floor_name}}</option>
                                @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Unit Type</label>
                                <select name="untype_id" class="form-control untype_id" required>
                                @foreach($unittypes as $unittype)
                                    <option value="{{$unittype->id}}">{{$unittype->untype_name}}</option>
                                @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- END UNIT -->
                        <div class="col-sm-12 text-center" style="margin-bottom: 20px;">
                            <h4><strong>Owner Details</strong></h4>
                        </div>
                        <!-- OWNER -->
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" class="form-control tenan_name" name="tenan_name" placeholder="Owner Name" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>KTP</label>
                                <input type="text" class="form-control tenan_idno" name="tenan_idno" placeholder="Owner KTP" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" class="form-control tenan_phone" name="tenan_phone" placeholder="Owner Phone Number" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>FAX</label>
                                <input type="text" class="form-control tenan_fax" name="tenan_fax" placeholder="Owner Fax Number">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="text" class="form-control tenan_email" name="tenan_email" placeholder="Owner Email Address" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Address</label>
                                <input type="text" class="form-control tenan_address" name="tenan_address" placeholder="Owner Address">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>NPWP</label>
                                <input type="text" class="form-control tenan_npwp" name="tenan_npwp" placeholder="Owner NPWP Number">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Tax Name (Nama NPWP)</label>
                                <input type="text" class="form-control tenan_taxname" name="tenan_taxname" placeholder="Owner NPWP Name">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Tax Address (Alamat NPWP)</label>
                                <input type="text" class="form-control tenan_tax_address" name="tenan_tax_address" placeholder="Owner NPWP Address">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>PPN</label>
                                &nbsp;&nbsp;<input type="checkbox" name="tenan_isppn" class="tenan_isppn" value="1" >
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>PKP</label>
                                &nbsp;&nbsp;<input type="checkbox" name="tenan_ispkp" class="tenan_ispkp" value="1" > 
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Own Unit Since</label>
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                    <input type="text" class="form-control datepicker unitow_start_date" name="unitow_start_date" placeholder="Own Unit Since" data-date-format="yyyy-mm-dd" required>
                                </div>
                            </div>
                        </div>
                        <!-- END OWNER -->
                        <div class="col-sm-12">
                            <button class="btn btn-info pull-right">Submit</button>
                            <button type="button" class="btn btn-danger pull-right" style="margin-right: 10px" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </form>
                <!-- end form -->
          </div>
        </div>

      </div>
    </div>

    <!-- End Modal -->

    <!-- Modal extra -->
    <div id="detailModal" class="modal fade" role="dialog">
      <div class="modal-dialog" style="width:900px">

        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Detail Unit</h4>
          </div>
          <div class="modal-body" id="detailUnitModalContent" style="padding: 20px 40px">  
          </div>
        </div>

      </div>
    </div>

    <!-- End Modal -->

    <!-- Modal select contract -->
    <div id="tenanModal" class="modal fade" role="dialog">
      <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
          
          <div class="modal-body" id="tenanModalContent">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
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
<!-- datepicker -->
<script type="text/javascript" src="{{ asset('plugins/datepicker/bootstrap-datepicker.js') }}"></script>
<script type="text/javascript">
        var entity = "Master Unit"; // nama si tabel, ditampilin di dialog
        var get_url = "{{route('unit.get')}}";
        var insert_url = "{{route('unit.insert')}}";
        var update_url = "{{route('unit.update')}}";
        var delete_url = "{{route('unit.delete')}}";

        $('.datepicker').datepicker({
            autoclose: true
        });

        $(function(){
            var dg = $('#dg').datagrid({
                url: get_url,
                pagination: true,
                remoteFilter: true, //utk jalanin search filter
                rownumbers: true,
                singleSelect: true,
                fitColumns: true,
                pageSize:50
            });
            dg.datagrid('enableFilter');
        });

        var row;
        function createNew(){
            $('#addUnitModal').modal("show");
        }

        function detail(){
            var row = $('#dg').datagrid('getSelected');   
            $.post('{{route('unit.modaldetail')}}', {id:row.id}, function(result){
                if(result.errorMsg) $.messager.alert('Warning',result.message);
                else{
                    $('#detailUnitModalContent').html(result);     
                    $('#detailModal').modal("show");
                }
            });
        }

        function editUnit(){
            var row = $('#dg').datagrid('getSelected');
            $.post('{{route('unit.ajaxdetail')}}', {id:row.id}, function(result){
                if(result.errorMsg) $.messager.alert('Warning',result.message);
                else{
                    $('.unit_id').val(row.id);
                    $('.unit_code').val(result.unit_code);
                    $('.unit_sqrt').val(result.unit_sqrt);
                    $('.virtual_account').val(result.virtual_account);
                    $('.floor_id').val(result.floor_id);
                    $('.untype_id').val(result.untype_id);
                    $('.meter_listrik').val(result.meter_listrik);
                    $('.meter_air').val(result.meter_air);
                    if(result.tenant){
                        $('.tenan_name').val(result.tenant.tenan_name);
                        $('.tenan_idno').val(result.tenant.tenan_idno);
                        $('.tenan_phone').val(result.tenant.tenan_phone);
                        $('.tenan_fax').val(result.tenant.tenan_fax);
                        $('.tenan_email').val(result.tenant.tenan_email);
                        $('.tenan_address').val(result.tenant.tenan_address);
                        $('.tenan_npwp').val(result.tenant.tenan_npwp);
                        $('.tenan_taxname').val(result.tenant.tenan_taxname);
                        $('.tenan_tax_address').val(result.tenant.tenan_tax_address);
                        if(result.tenant.tenan_ispkp) $('.tenan_ispkp').prop('checked', true);
                        if(result.tenant.tenan_isppn) $('.tenan_isppn').prop('checked', true);
                        $('.unitow_start_date').val(result.unitow_start_date);
                    }
                    $('#editUnitModal').modal("show");
                }
            },'json');
        }

        $('#formAddUnit').submit(function(e){
            e.preventDefault();
            if($('input[name=owner]:checked').val() == 1){
                if($('input[name=tenan_id]').val() == ''){ 
                    $.messager.alert('Warning','Please Choose Tenan first');
                    return false;
                }
            }

            $.post('{{route('unit.insert')}}', $(this).serialize(), function(result){
                // console.log(result);
                if(result.errorMsg) $.messager.alert('Warning',result.message);
                if(result.success){
                    $.messager.alert('Success',result.message);
                    $('#addUnitModal').modal("hide");
                    $('#dg').datagrid('reload');
                }
            },'json');
        });

        $('#formEditUnit').submit(function(e){
            e.preventDefault();
            $.post('{{route('unit.update')}}', $(this).serialize(), function(result){
                // console.log(result);
                if(result.errorMsg) $.messager.alert('Warning',result.message);
                if(result.success){
                    $.messager.alert('Success',result.message);
                    $('#editUnitModal').modal("hide");
                    $('#dg').datagrid('reload');
                }
            },'json');
        });

        $('input[name=owner]').change(function(){
            if($(this).val() == 1){
                $('input[name=tenan_name]').removeAttr('required');
                $('input[name=tenan_idno]').removeAttr('required');
                $('input[name=tenan_phone]').removeAttr('required');
                $('input[name=tenan_email]').removeAttr('required');
                $('#newowner').addClass('hidden');
                $('#oldowner').removeClass('hidden');
            }else{
                $('input[name=tenan_name]').attr('required','required');
                $('input[name=tenan_idno]').attr('required','required');
                $('input[name=tenan_phone]').attr('required','required');
                $('input[name=tenan_email]').attr('required','required');
                $('#newowner').removeClass('hidden');
                $('#oldowner').addClass('hidden');
            }
        });

        // tenan
        $('#chooseTenanButton').click(function(){
            $('#tenanModal').modal("show");
            currenturl = '{{route('tenant.popup')}}';
            $.post(currenturl,null, function(data){
                $('#tenanModalContent').html(data);
            });
        });

        $(document).delegate('#searchTenant','submit',function(e){
            e.preventDefault();
            var data = $('#searchTenant').serialize();
            currenturl = '{{route('tenant.popup')}}';
            $.post(currenturl, data, function(data){
                $('#tenanModalContent').html(data);
            });
        });

        // paging
        $(document).delegate('.pagination li a','click',function(e){
            e.preventDefault();
            currenturl = $(this).attr('href');
            $.post(currenturl, null, function(data){
                $('#tenanModalContent').html(data);
            });
        });

        $(document).delegate('#chooseTenant','click',function(e){
            e.preventDefault();
            var tenanid = $('input[name="tenant"]:checked').val();
            var tenanname = $('input[name="tenant"]:checked').data('name');
            $('#txtTenanId').val(tenanid);
            $('#txtTenan').val(tenanname);
            $('#tenanModalContent').text('');
            $('#tenanModal').modal("hide");
            
            $('input[name=tenan_name]').removeAttr('disabled');
                $('input[name=tenan_idno]').removeAttr('disabled');
                $('input[name=tenan_phone]').removeAttr('disabled');
                $('input[name=tenan_email]').removeAttr('disabled');
        });
</script>
<script src="{{asset('js/jeasycrud.js')}}"></script>
@endsection
