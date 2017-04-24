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
          		<table id="dg" title="Aging Invoice" class="easyui-datagrid" style="width:100%;min-height:500px;" toolbar="#toolbar">
                    <!-- kolom -->
                    <thead>
                        <tr>
                            <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                            <th field="unit_code" width="50" sortable="true">Unit</th>
                            <th field="tenan_name" width="150" sortable="true">Nama Tenan</th>
                            <th field="total" width="100" sortable="true" align="right">Total</th>
                            <th field="ag30" width="100" sortable="true" align="right">1 - 30 Hari</th>
                            <th field="ag60" width="100" sortable="true" align="right">31 - 60 Hari</th> 
                            <th field="ag90" width="100" sortable="true" align="right">61 - 90 Hari</th>
                            <th field="agl180" width="100" sortable="true" align="right">> 90 Hari</th>         
                        </tr>
                    </thead>
                </table>
                <!-- end table -->
                
                <!-- icon2 atas table -->
                <div id="toolbar">
                    <a href="{{ url('aging/downloadAgingExcel') }}" class="easyui-linkbutton" plain="false">Download Report</a>
                </div>
                <!-- end icon -->

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
        pageSize:100,
        pageList: [100,500,1000],
        detailFormatter:function(index,row){
            return '<div style="padding:2px"><table class="ddv"></table></div>';
        },
        onExpandRow: function(index,row){
            var ddv = $(this).datagrid('getRowDetail',index).find('table.ddv');
            ddv.datagrid({
                url: get_url2+"?id="+row.ids,
                singleSelect:true,
                rownumbers:true,
                loadMsg:'Please Wait',
                height:'auto',
                columns:[[
                    {field:'inv_number',title:'Inv Number',width:100},
                    {field:'invtp_name',title:'Invoice Type',width:150},
                    {field:'tanggal',title:'Inv Date',width:100},
                    {field:'tanggaldue',title:'Inv Due Date',width:100},
                    {field:'inv_amount',title:'Amount',width:100,formatter:addCommas,align:'right'},
                    {field:'inv_outstanding',title:'Sisa',width:100,formatter:addCommas,align:'right'},
                    {field:'inv_post',title:'Posting',width:50,align:'center',formatter:formatPost}
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
   $('#dg').datagrid('enableFilter');
});
function formatPost(val,row){
    if (val == true){
        return '<span style="color:red;">Yes</span>';
    } else {
        return 'No';
    }
}
function addCommas(nStr,row)
{
    nStr += '';
    x = nStr.split('.');
    x1 = x[0];
    x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
}    
</script>
<script src="{{asset('js/jeasycrud.js')}}"></script>
@endsection
