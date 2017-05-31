@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Tenant
@endsection

<!-- page title -->
@section('contentheader_title')
   Tenant / Owner List
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
        <li class="active">Tenant / Owner</li>
    </ol>
@stop

@section('main-content')
<div class="row">
	<div class="col-md-12">
  		<!-- content -->

        <div class="row">
            <div class="col-sm-4">
                <table width="100%">
                    <tr>
                        <td><label class="radio-inline"><input type="radio" name="type" value="owner">Owner</label></td>
                        <td><label class="radio-inline"><input type="radio" name="type" value="tenant">Tenant</label></td>
                        <td><button type="button" class="btn btn-info" id="filterTenant">filter</button></td>
                    </tr>
                </table>
            </div>
        </div>
        <br>
        <!-- template tabel -->
        <div style="height:400px">
  		<table id="dg" title="Tenant / Owner" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
            <!-- kolom -->
            <thead>
                <tr>
                    <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                    <th field="tenan_code" width="50" sortable="true">Tenant Code</th>
                    <th field="tenan_name" width="50" sortable="true">Tenant Name</th>
                    <th field="tenan_phone" width="50" sortable="true">Phone</th>
                    <th field="tenan_email" width="50" sortable="true">Email</th>
                    <th field="tent_name" width="50" sortable="true">Tenant Type</th>
                    <th field="action" width="50">Action</th>
                </tr>
            </thead>
        </table>
        </div>
        <!-- end table -->
        
        <!-- icon2 atas table -->
        <div id="toolbar">
            @if(Session::get('role')==1 || in_array(28,Session::get('permissions')))
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newUser()">New</a>
            @endif
            @if(Session::get('role')==1 || in_array(29,Session::get('permissions')))
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editUser()">Edit</a>
            @endif
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-search" plain="true" onclick="detail()">View Detail</a>
            @if(Session::get('role')==1 || in_array(30,Session::get('permissions')))
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="destroyUser()">Remove</a>
            @endif
        </div>
        <!-- end icon -->
    
        <!-- hidden form buat create edit -->
        <div id="dlg" class="easyui-dialog" style="width:60%"
                closed="true" buttons="#dlg-buttons">
            <form id="fm" method="post" style="margin:0;padding:20px 50px">
                <div style="margin-bottom:20px;font-size:14px;border-bottom:1px solid #ccc">Input Data</div>
                <!-- <div style="margin-bottom:10px">
                    <input name="tenan_code" class="easyui-textbox" label="Code:" style="width:100%" data-options="required:true,validType:'length[0,15]'">
                </div> -->
                <div style="margin-bottom:10px">
                    <input name="tenan_name" class="easyui-textbox" label="Name:" style="width:100%" data-options="required:true,validType:'length[0,80]'">
                </div>
                <div style="margin-bottom:10px">
                    <input name="tenan_idno" class="easyui-textbox" label="KTP No (Identity No):" style="width:100%" data-options="required:true,validType:'length[0,20]'">
                </div>
                <div style="margin-bottom:10px">
                    <input name="tenan_phone" class="easyui-textbox" label="Phone:" style="width:100%" data-options="required:true,validType:'length[0,15]'">
                </div>
                <div style="margin-bottom:10px">
                    <input name="tenan_fax" class="easyui-textbox" label="Fax:" style="width:100%" data-options="validType:'length[0,20]'">
                </div>
                <div style="margin-bottom:10px">
                    <input name="tenan_email" class="easyui-textbox" label="Email:" style="width:100%" data-options="required:true,validType:'length[0,80]'">
                </div>
                <div style="margin-bottom:10px">
                    <input name="tenan_address" class="easyui-textbox" label="Address:" style="width:100%" data-options="required:true,validType:'length[0,150]'">
                </div>
                <div style="margin-bottom:10px">
                    <input name="tenan_npwp" class="easyui-textbox" label="NPWP:" style="width:100%" data-options="validType:'length[0,15]'">
                </div>
                <div style="margin-bottom:10px">
                    <input name="tenan_taxname" class="easyui-textbox" label="Tax Name:" style="width:100%" data-options="validType:'length[0,50]'">
                </div>
                <div style="margin-bottom:10px">
                    <input name="tenan_tax_address" class="easyui-textbox" label="Tax Address:" style="width:100%" data-options="validType:'length[0,150]'">
                </div>
                <div style="margin-bottom:10px">
                    <label class="textbox-label textbox-label-before" for="_easyui_textbox_input10" style="text-align: left; height: 27px; line-height: 27px;">Tenant Type</label>
                    <select id="tentype" name="tent_id" style="width: 50%; height: 30px; border-radius: 4px; border-color: #95B8E7;" required>
                        @foreach($tenantTypes as $tent)
                        <option value="{{$tent->id}}" data-owner="{{$tent->tent_isowner}}">{{$tent->tent_name}}</option>
                        @endforeach           
                    </select>
                </div>
                <div style="margin-bottom:10px">
                    <label class="textbox-label textbox-label-before" for="_easyui_textbox_input10" style="text-align: left; height: 27px; line-height: 27px;">PPN/NON PPN</label>
                    <input type="checkbox" name="tenan_isppn" value="1" >
                </div>
                <div style="margin-bottom:10px" id="unitColumn">
                    <label class="textbox-label textbox-label-before" for="_easyui_textbox_input10" style="text-align: left; height: 27px; line-height: 27px;">Unit Owned</label>
                    <input type="hidden" name="unit_id">
                    <input type="hidden" name="current_unit_id">
                    <input type="text" id="unitView" disabled style="height: 35px;">
                    <button type="button" class="btn btn-info" id="unitButton" disabled style="height: 35px; margin-top: -4px; border-radius: 0; margin-left: -4px;">Search Unit</button>
                </div>
                <div style="margin-bottom:10px" id="unitStartDateColumn">
                    <label class="textbox-label textbox-label-before" for="_easyui_textbox_input10" style="text-align: left; height: 27px; line-height: 27px;">Unit Start Owned</label>
                    <input type="text" id="unitStartDate" name="unitow_start_date" disabled class="datepicker" data-date-format="yyyy-mm-dd">
                </div>
                
            </form>
        </div>
        <div id="dlg-buttons">
            <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveUser()" style="width:90px">Save</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg').dialog('close')" style="width:90px">Cancel</a>
        </div>
        <!-- end form -->


         <!-- Modal select tenant -->
        <div id="unitModal" class="modal fade" role="dialog" style="z-index:9999">
          <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
              
              <div class="modal-body" id="unitModalContent">
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              </div>
            </div>

          </div>
        </div>
        <!-- End Modal -->

        <!-- Modal select unit -->
        <div id="detailModal" class="modal fade" role="dialog">
          <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
              
              <div class="modal-body" id="detailModalContent">
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              </div>
            </div>

          </div>
        </div>
        <!-- End Modal -->

        <!-- Modal select unit -->
        <div id="addUnitModal" class="modal fade" role="dialog">
          <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
              
              <div class="modal-body" id="addUnitModalContent">
                    <center>
                        <h3>Add Unit</h3>
                        <br>
                        <p>Owned Start From Date</p>
                        <div style="width:50%">
                        <input type="text" id="startDate" name="contr_startdate" required="required" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd">
                        </div>
                    <br><br>
                    <p>Unit</p>
                    <input type="hidden" name="addUnitTenanId" id="addUnitTenanId">
                    <input type="hidden" name="addUnitId" id="addUnitId">
                    <input type="text" id="addUnitView" disabled style="height: 35px;">
                    <button type="button" class="btn btn-info" id="addUnitButton" style="height: 35px; margin-top: -3px; border-radius: 0; margin-left: -4px;">Search Unit</button>
                    <br><br>
                    <button type="button" class="btn btn-default" id="addUnitSubmit">Submit</button>
                    </center>
            </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              </div>
            </div>

        </div>
        </div>
  		<!-- content -->
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
        var entity = "Master Tenant"; // nama si tabel, ditampilin di dialog
        var get_url = "{{route('tenant.get')}}";
        var insert_url = "{{route('tenant.insert')}}";
        var update_url = "{{route('tenant.update')}}";
        var delete_url = "{{route('tenant.delete')}}";
        var flagInsert = false;

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

        $('#filterTenant').click(function(){
            $('#dg').datagrid('load', {
                type:$('input[name=type]:checked').val()
            });
            $('#dg').datagrid('reload');
        });

        $('#tentype').change(function(){
            if($(this).find('option:checked').data('owner') == 1){
                $('#unitButton').removeAttr('disabled');
                $('#unitStartDate').removeAttr('disabled');
            }else{
                $('#unitButton').attr('disabled','disabled');
                $('#unitStartDate').attr('disabled','disabled');
                $('#unitView').val('');
                $('input[name=unit_id]').val('');
            }
        });

        var currenturl;
        $('#addUnitButton,#unitButton').click(function(){
            $('#unitModal').modal('show');
            currenturl = '{{route('unit.popup',['all'=>1])}}';
            $.post(currenturl,null, function(data){
                $('#unitModalContent').html(data);
            });
        });

        $(document).delegate('#searchUnit','submit',function(e){
            e.preventDefault();
            var data = $('#searchUnit').serialize();
            currenturl = '{{route('unit.popup',['all'=>1])}}';
            $.post(currenturl, data, function(data){
                $('#unitModalContent').html(data);
            });
        });

        $(document).delegate('#chooseUnit','click',function(e){
            e.preventDefault();
            var unitid = $('input[name="unit"]:checked').val();
            var unitname = $('input[name="unit"]:checked').data('name');
            if(flagInsert){
                $('#unitView').val(unitname);
                $('input[name=unit_id]').val(unitid);
            }else{
                $('#addUnitView').val(unitname);
                $('#addUnitId').val(unitid);
            }

            $('#unitModalContent').text('');
            $('#unitModal').modal("hide");
        });

        function newUser(){
            flagInsert = true;
            console.log('create');
            $('#unitColumn').show();
            $('#unitStartDateColumn').show();
            $('#unitStartDate').attr('disabled','disabled');
            $('#unitStartDate').val('');
            $('#dlg').dialog('open').dialog('center').dialog('setTitle','New '+entity);
            $('#fm').form('clear');
            url = insert_url;
        }
        function editUser(){
            console.log('edit');
            $('#unitColumn').hide();
            $('#unitButton').attr('disabled','disabled');
            $('#unitStartDateColumn').hide();
            
            var row = $('#dg').datagrid('getSelected');
            if (row){
                // ajax
                $.post('{{route('tenant.edit')}}',{id:row.id},function(data){
                    console.log(data);
                    if(data.tenan_isppn) $('input[name=tenan_isppn]').prop('checked', true);
                    if(data.tenan_ispkp) $('input[name=tenan_ispkp]').prop('checked', true);
                    $('#tentype').val(data.tent_id);
                    // if(data.unit_id){ 
                    //     $('#unitView').val(data.unit_code+" "+data.unit_name);
                    //     $('input[name=unit_id]').val(data.unit_id);
                    //     $('input[name=current_unit_id]').val(data.unit_id);
                    //     $('#unitButton').removeAttr('disabled');
                    // }else{
                    //     $('#unitView').val('');
                    //     $('input[name=unit_id]').val('');
                    //     $('input[name=current_unit_id]').val('');
                    //     $('#unitButton').attr('disabled','disabled');
                    // }
                });

                $('#dlg').dialog('open').dialog('center').dialog('setTitle','Edit '+entity);
                $('#fm').form('load',row);
                url = update_url+'?id='+row.id;
            }
        }
        function saveUser(){
            console.log('save or edit');
            var type;
            var row = $('#dg').datagrid('getSelected');
            if(row) type = 'edit';
            else type = 'add';

            var owned = $('#tentype').find('option:checked').data('owner');
            var unitid = $('input[name=unit_id]').val();
            var lanjut = false;
            if(owned == 1){
                if(unitid == "" || unitid == null) $.messager.alert('Warning','Unit must be choosed first');
                else lanjut = true;
            }else{
                lanjut = true;
            }

            if(lanjut){
                $('#fm').form('submit',{
                    url: url,
                    onSubmit: function(){
                        return $(this).form('validate');
                    },
                    success: function(result){
                        var result = eval('('+result+')');
                        if (result.errorMsg){
                            $.messager.show({
                                title: 'Error',
                                msg: result.errorMsg
                            });
                        } else {
                            if(type == 'edit') $.messager.alert('Warning','Update Success');
                            else $.messager.alert('Warning','Insert Success');
                            $('#dlg').dialog('close');      // close the dialog
                            $('#dg').datagrid('reload');    // reload the user data
                        }
                    },
                    error: function (request, status, error) {
                        alert(request.responseText);
                      }
                });
            }
        }
        function destroyUser(){
            console.log('destroy');
            var row = $('#dg').datagrid('getSelected');
            if (row){
                $.messager.confirm('Confirm','Are you sure you want to destroy this Tenant?',function(r){
                    if (r){
                        $.post(delete_url,{id:row.id},function(result){
                            if (result.success){
                                $.messager.alert('Success','Remove tenant Success');
                                $('#dg').datagrid('reload');    // reload the user data
                            } else {
                                $.messager.show({   // show error message
                                    title: 'Error',
                                    msg: result.errorMsg
                                });
                                // $.messager.alert('Warning','The warning message');
                            }
                        },'json');
                    }
                });
            }
        }
        function detail(){
            var row = $('#dg').datagrid('getSelected');
            if (row){
                $('#detailModal').modal('show');
                $.post('{{route('tenant.modaldt')}}',{id: row.id}, function(data){
                    $('#detailModalContent').html(data);
                });
            }
        }
        $(document).delegate('.deleteUnit','click',function(){
            var unitid = $(this).data('unit');
            var tenanid = $(this).data('tenan');
            var currentClass = $(this);
            var r = confirm("Are you sure want to delete this unit from this tenant ?");
            if (r == true) {
                $.post('{{route('tenant.deleteunit')}}',{unitid: unitid, tenanid: tenanid}, function(result){
                    if (result.success){
                        $.messager.alert('Success','Remove unit from tenant Success');
                        currentClass.closest('table').remove();
                        // $('#dg').datagrid('reload'); 
                    } else {
                        $.messager.show({   // show error message
                            title: 'Error',
                            msg: result.errorMsg
                        });
                        // $.messager.alert('Warning','The warning message');
                    }
                });
            }
        });

    $(document).delegate('.addUnit','click',function(){
        flagInsert = false;
        var id = $(this).data('id');
        $('#addUnitTenanId').val(id);
        $('#addUnitModal').modal('show');
    });

    $('#addUnitSubmit').click(function(){
        var tenanid = $('#addUnitTenanId').val();
        var unitid = $('#addUnitId').val();
        var date = $('#startDate').val();
        if(unitid == ''){ $.messager.alert('Warning','Unit must be choosed first'); }
        else if(date == ''){ $.messager.alert('Warning','Date must be choosed first'); }
        else{
            $.post('{{route('tenant.addunit')}}',{unitid:unitid, tenanid:tenanid, date:date},function(result){
                    if (result.success){
                        $.messager.alert('Success','Insert Success');
                        $('#startDate').val('');
                        $('#addUnitView').val('');
                        $('#addUnitId').val(''); 
                        $('#addUnitModal').modal('hide');
                    } else {
                        $.messager.show({   // show error message
                            title: 'Error',
                            msg: result.errorMsg
                        });
                    }
            });
        }
    });

    $('.datepicker').datepicker({
            autoclose: true
        });
</script>

@endsection
