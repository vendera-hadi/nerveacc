@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Aging Invoice
@endsection

<!-- page title -->
@section('contentheader_title')
   Aging Invoice
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
        <li class="active">Aging Invoice</li>
    </ol>
@stop

@section('main-content')
	<div class="container spark-screen">
		<div class="row">
			<div class="col-md-11">
          		<!-- content -->

                <!-- template tabel -->
          		<table id="dg" title="Aging Invoice" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
                    <!-- kolom -->
                    <thead>
                        <tr>
                            <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                            <th field="contr_no" width="100" sortable="true">No. Contract</th>
                            <th field="contr_startdate" width="100" sortable="true">Start Date</th>
                            <th field="contr_enddate" width="100" sortable="true">End Date</th>  
                            <th field="contr_status" width="50" sortable="true">Contract Status</th>
                            <th field="tenan_code" width="50" sortable="true">No Tenan</th>       
                        </tr>
                    </thead>
                </table>
                <!-- end table -->
                
                <!-- icon2 atas table -->
                <div id="toolbar">
                    
                </div>
                <!-- end icon -->
            
                <!-- hidden form buat create edit -->
                <div id="dlg" class="easyui-dialog" style="width:60%"
                        closed="true" buttons="#dlg-buttons">
                    <form id="fm" method="post" novalidate style="margin:0;padding:20px 50px">
                        <div style="margin-bottom:20px;font-size:14px;border-bottom:1px solid #ccc">Input Data</div>
                        <div style="margin-bottom:10px">
                            <input name="costd_name" class="easyui-textbox" label="Cost Name:" style="width:100%" data-options="required:true,validType:'length[0,100]'">
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
                            <input id="cc" class="easyui-combobox" required="true" name="cost_id" style="width:100%" label="Cost Item:" data-options="valueField:'id',textField:'text',url:'{{route('cost_detail.options')}}'">
                        </div>
                        <div style="margin-bottom:10px">
                            <select id="cc" class="easyui-combobox" required="true" name="costd_ismeter" label="Active Meter:" style="width:300px;">
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
var entity = "List Aging Invoice"; // nama si tabel, ditampilin di dialog
var get_url = "{{route('aging.get')}}";
var get_url2 = "{{route('aging.getdetail')}}";

$(function(){
   $('#dg').datagrid({
        view: detailview,
        url: get_url,
        pagination: true,
        remoteFilter: true,
        rownumbers: true,
        singleSelect: true,
        fitColumns: true,
        detailFormatter:function(index,row){
            return '<div style="padding:2px"><table class="ddv"></table></div>';
        },
        onExpandRow: function(index,row){
            var ddv = $(this).datagrid('getRowDetail',index).find('table.ddv');
            ddv.datagrid({
                url: get_url2+"?id="+row.id,
                singleSelect:true,
                rownumbers:true,
                loadMsg:'',
                height:'auto',
                columns:[[
                    {field:'inv_number',title:'Inv Number',width:100},
                    {field:'inv_date',title:'Inv Date',width:100},
                    {field:'inv_duedate',title:'Inv Due Date',width:100},
                    {field:'inv_amount',title:'Amount',width:100},
                    {field:'tenan_code',title:'No. Tenan',width:100},
                    {field:'invtp_name',title:'Invoice Type',width:150},
                    {field:'inv_post',title:'Posting',width:50,align:'center',formatter:formatPost},
                    {field:'ag',title:'(1-30) Hari',align:'center',width:100,formatter:check30},
                    {field:'ag1',title:'(31-60) Hari',align:'center',width:100,formatter:check60},
                    {field:'ag2',title:'(61-90) Hari',align:'center',width:100,formatter:check90},
                    {field:'ag3',title:'(91-180) Hari',align:'center',width:100,formatter:check180},
                    {field:'ag4',title:'> 180 Hari',align:'center',width:150,formatter:check0}
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
function formatPost(val,row){
    if (val == true){
        return '<span style="color:red;">Yes</span>';
    } else {
        return 'No';
    }
}
function check30(val,row){
    if (val >0 && val <=30){
        return '<span style="color:red;">V</span>';
    } else {
        return '';
    }
}
function check60(val,row){
    if (val >30 && val <=60){
        return '<span style="color:red;">V</span>';
    } else {
        return '';
    }
}
function check90(val,row){
    if (val >60 && val <=90){
        return '<span style="color:red;">V</span>';
    } else {
        return '';
    }
}
function check180(val,row){
    if (val >90 && val <=180){
        return '<span style="color:red;">V</span>';
    } else {
        return '';
    }
}
function check0(val,row){
    if (val >180){
        return '<span style="color:red;">V</span>';
    } else {
        return '';
    }
}      
</script>
<script src="{{asset('js/jeasycrud.js')}}"></script>
@endsection
