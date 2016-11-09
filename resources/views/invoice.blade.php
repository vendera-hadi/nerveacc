@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    List Invoice
@endsection

<!-- page title -->
@section('contentheader_title')
   List Invoice
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
        <li class="active">List Invoice</li>
    </ol>
@stop

@section('main-content')
	<div class="container spark-screen">
		<div class="row">
			<div class="col-md-11">
          		<!-- content -->

                <!-- template tabel -->
          		<table id="dg" title="List Invoice" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
                    <!-- kolom -->
                    <thead>
                        <tr>
                            <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                            <th field="inv_number" width="100" sortable="true">No.Invoice</th>
                            <th field="contr_id" width="100" sortable="true">No Kontrak</th>
                            <th field="tenan_name" width="100" sortable="true">Nama Tenan</th>  
                            <th field="inv_data" width="50" sortable="true">Tgl Invoice</th>
                            <th field="inv_duedate" width="50" sortable="true">Jatuh Tempo</th>
                            <th field="inv_amount" width="50" sortable="true">Amount</th>
                            <th field="inv_ppn" width="50" sortable="true">PPN</th>
                            <th field="inv_ppn_amount" width="50" sortable="true">PPN Amount</th> 
                            <th field="invtp_name" width="100" sortable="true">Jenis Invoice</th>
                            <th field="inv_post" width="50" sortable="true">Posting</th>       
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
<script type="text/javascript" src="http://www.jeasyui.com/easyui/datagrid-detailview.js"></script>
<script type="text/javascript">
var entity = "List Invoice"; // nama si tabel, ditampilin di dialog
var get_url = "{{route('invoice.get')}}";
var get_url2 = "{{route('invoice.getdetail')}}";

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
            fitColumns:true,
            singleSelect:true,
            rownumbers:true,
            loadMsg:'Loading',
            height:'auto',
            toolbar:toolbar,
            columns:[[
                    {field:'invdt_amount',title:'Invoice Amount',width:100},
                    {field:'invdt_note',title:'Invoice Note',width:100},
                    {field:'prdmet_id',title:'Period Meter',width:100},
                    {field:'prd_billing_date',title:'Billing Date',width:100},
                    {field:'meter_start',title:'Meter Start',width:100},
                    {field:'meter_end',title:'Meter End',width:100},
                    {field:'meter_used',title:'Meter Used',width:100},
                    {field:'meter_cost',title:'Meter Cost',width:100}
                ]],
                onResize:function(){
                    $('#dg').datagrid('fixDetailRowHeight',index);
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
</script>
<script src="{{asset('js/jeasycrud.js')}}"></script>
@endsection
