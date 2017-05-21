@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Component Billing
@endsection

<!-- page title -->
@section('contentheader_title')
   Master Component Billing
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
        <li class="active">Master Component Billing</li>
    </ol>
@stop

@section('main-content')
	<div class="container spark-screen">
		<div class="row">
			<div class="col-md-11">
          		<!-- content -->

                <!-- template tabel -->
          		<table id="dg" title="Master Component Billing" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
                    <!-- kolom -->
                    <thead>
                        <tr>
                            <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                            <th field="cost_code" width="50" sortable="true">Component Code</th>
                            <th field="cost_name" width="50" sortable="true">Component Name</th>
                            <th field="cost_coa_code" width="50" sortable="true">Coa Code</th>
                            <th field="ar_coa_code" width="50" sortable="true">Coa AR Code</th>
                            <th field="cost_isactive" width="50" sortable="true">Active</th>
                        </tr>
                    </thead>
                </table>
                <!-- end table -->
                
                <!-- icon2 atas table -->
                <div id="toolbar">
                    @if(Session::get('role')==1 || in_array(45,Session::get('permissions')))
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newUser()">New</a>
                    @endif
                    @if(Session::get('role')==1 || in_array(46,Session::get('permissions')))
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editUser()">Edit</a>
                    @endif
                    @if(Session::get('role')==1 || in_array(47,Session::get('permissions')))
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
                            <input name="cost_code" class="easyui-textbox" label="Component Code:" style="width:100%" data-options="required:true,validType:'length[0,5]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="cost_name" class="easyui-textbox" label="Component Name:" style="width:100%" data-options="required:true,validType:'length[0,150]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input id="cc" class="easyui-combobox" required="true" name="cost_coa_code" style="width:100%" label="Coa Component:" data-options="valueField:'id',textField:'text',url:'{{route('cost_item.getOptionsCoa')}}'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input id="cc2" class="easyui-combobox" required="true" name="ar_coa_code" style="width:100%" label="Coa AR Component:" data-options="valueField:'id',textField:'text',url:'{{route('cost_item.getOptionsCoa')}}'">
                        </div>
                        <div style="margin-bottom:10px">
                            <select id="cc" class="easyui-combobox" required="true" name="cost_isactive" label="Active:" style="width:300px;">
                                <option value="true" >yes</option>
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

                <!-- hidden form buat create edit -->
                <div id="dlg2" class="easyui-dialog" style="width:60%"
                        closed="true" buttons="#dlg-buttons">
                    <form id="fm2" method="post" novalidate style="margin:0;padding:20px 50px">
                        <div style="margin-bottom:20px;font-size:14px;border-bottom:1px solid #ccc">Input Data</div>
                        <div style="margin-bottom:10px">
                            <input name="costd_name" class="easyui-textbox" label="Component Name:" style="width:100%" data-options="required:true,validType:'length[0,100]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="costd_rate" class="easyui-textbox" label="Rate:" style="width:100%" data-options="required:true,validType:'length[0,9]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="costd_burden" class="easyui-textbox" label="Biaya Abodemen:" style="width:100%" data-options="required:true,validType:'length[0,9]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="costd_admin" class="easyui-textbox" label="Biaya Admin:" style="width:100%" data-options="required:true,validType:'length[0,9]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="costd_unit" class="easyui-textbox" label="Satuan:" style="width:100%" data-options="required:true,validType:'length[0,10]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input id="cc" class="easyui-combobox" required="true" name="cost_id" style="width:100%" label="Component Billing:" data-options="valueField:'id',textField:'text',url:'{{route('cost_detail.options')}}'">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="daya" class="easyui-textbox" label="Daya:" style="width:100%" data-options="validType:'length[0,100]'">
                        </div>
                        <div style="margin-bottom:10px">
                            <select id="cc" class="easyui-combobox" required="true" name="costd_ismeter" label="Komponen Ber-Meter:" style="width:300px;">
                                <option value="true" >yes</option>
                                <option value="false">no</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div id="dlg-buttons">
                    <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveUser2()" style="width:90px">Save</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg2').dialog('close')" style="width:90px">Cancel</a>
                </div>
                <!-- end form -->

          		<!-- content -->
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
        var entity = "Master Component Billing"; // nama si tabel, ditampilin di dialog
        var get_url = "{{route('cost_item.get')}}";
        var insert_url = "{{route('cost_item.insert')}}";
        var update_url = "{{route('cost_item.update')}}";
        var delete_url = "{{route('cost_item.delete')}}";
        var entity2 = "Master Cost Detail"; 
        var get_url2 = "{{route('cost_item.cost_detail')}}";
        var insert_url2 = "{{route('cost_detail.insert')}}";
        var update_url2 = "{{route('cost_detail.update')}}";
        var delete_url2 = "{{route('cost_detail.delete')}}";
        $(function(){
            $('#dg').datagrid({
                view: detailview,
                url: get_url,
                pagination: true,
                remoteFilter: true,
                rownumbers: true,
                singleSelect: true,
                fitColumns: true,
                pageSize:100,
                pageList: [100,500,1000],
                detailFormatter:function(index,row){
                    return '<div style="padding:2px"><table class="ddv"></table></div>';
                },
                <?php 
                    $detailcommand = '';
                    if(\Session::get('role')==1 || in_array(49,\Session::get('permissions'))){
                        $detailcommand .= '<button onclick="editRecord(this)">Edit</button>&nbsp;';
                    }
                    if(\Session::get('role')==1 || in_array(50,\Session::get('permissions'))){
                        $detailcommand .= '<button onclick="RemoveRecord(this)">Remove</button>';
                    }
                ?>
                onExpandRow: function(index,row){
                    var ddv = $(this).datagrid('getRowDetail',index).find('table.ddv');
                    ddv.datagrid({
                        url: get_url2+"?id="+row.id,
                        fitColumns:true,
                        singleSelect:true,
                        rownumbers:true,
                        loadMsg:'Loading',
                        height:'auto',
                        @if(Session::get('role')==1 || in_array(48,\Session::get('permissions')))
                        toolbar:toolbar,
                        @endif
                        columns:[[
                            {field:'costd_name',title:'Component Name',width:100},
                            {field:'costd_rate',title:'Cost Rate',width:100},
                            {field:'costd_burden',title:'Abodemen Cost',width:100},
                            {field:'costd_admin',title:'Admin Cost',width:100},
                            {field:'costd_unit',title:'Satuan',width:100},
                            {field:'costd_ismeter',title:'Meter Status',width:100},
                            {field:'daya',title:'Daya',width:100},
                            {field: 'action', title: 'Action',
                                 formatter:function(value,row,index)
                                 {
                                    var s = '{!!$detailcommand!!}';
                                    return s;
                                }
                            }
                        ]],
                        onResize:function(){
                            $('#dg').datagrid('fixDetailRowHeight',index);
                        },
                        onLoadSuccess:function(){
                            setTimeout(function(){
                                $('#dg').datagrid('fixDetailRowHeight',index);
                            },0);
                        }
                    });
                    $('#dg').datagrid('fixDetailRowHeight',index);
                }
            });
        });

        var toolbar = [{
            text:'New',
            iconCls:'icon-add',
            handler:function(){
                $('#dlg2').dialog('open').dialog('center').dialog('setTitle','New '+entity2);
                $('#fm2').form('clear');
                url = insert_url2;
            }
        }];

        function editRecord(btn){
            var tr = $(btn).closest('tr.datagrid-row');
            var index = parseInt(tr.attr('datagrid-row-index'));
            var dg = tr.closest('div.datagrid-view').children('table');
            var row = dg.datagrid('getRows')[index];
            if (row){
                $('#dlg2').dialog('open').dialog('center').dialog('setTitle','Edit '+entity2);
                $('#fm2').form('load',row);
                url = update_url2+'?id='+row.id;
            }
        }

        function saveUser2(){
            $('#fm2').form('submit',{
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
                        $('#dlg2').dialog('close');      // close the dialog
                        $('#dg').datagrid('reload');    // reload the user data
                    }
                },
                error: function (request, status, error) {
                    alert(request.responseText);
                  }
            });
        }

        function RemoveRecord(btn){
            var tr = $(btn).closest('tr.datagrid-row');
            var index = parseInt(tr.attr('datagrid-row-index'));
            var dg = tr.closest('div.datagrid-view').children('table');
            var row = dg.datagrid('getRows')[index];
            if (row){
                $.messager.confirm('Confirm','Are you sure you want to destroy this '+entity2+'?',function(r){
                    if (r){
                        $.post(delete_url2,{id:row.id},function(result){
                            if (result.success){
                                $('.ddv').datagrid('reload');    // reload the user data
                            } else {
                                $.messager.alert('Warning','The warning message');
                            }
                        },'json');
                    }
                });
            }
        }
</script>
<script src="{{asset('js/jeasycrud.js')}}"></script>
@endsection
