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
                <form id="search">
                <div class="row" style="margin-bottom:20px">
                    <div class="col-sm-4">
                        <input type="text" class="form-control" name="q" placeholder="Search Invoice No or Contract or Tenan Name">
                    </div>
                    <div class="col-sm-4">
                        <button class="btn btn-info">Cari</button>
                    </div>
                </div>
                </form>

                <!-- template tabel -->
          		<table id="dg" title="List Invoice" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
                    <!-- kolom -->
                    <thead>
                        <tr>
                            <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                            <th field="inv_number" width="100" sortable="true">No.Invoice</th>
                            <th field="contr_no" width="100" sortable="true">No Kontrak</th>
                            <th field="tenan_name" width="100" sortable="true">Nama Tenan</th>
                            <th field="unit" width="100" sortable="true">Unit</th>  
                            <th field="inv_date" width="50" sortable="true">Tgl Invoice</th>
                            <th field="inv_duedate" width="50" sortable="true">Jatuh Tempo</th>
                            <th field="inv_amount" width="50" sortable="true">Amount</th>
                            <th field="inv_ppn_amount" width="50" sortable="true">+ 10% PPN Amount</th> 
                            <th field="invtp_name" width="100" sortable="true">Jenis Invoice</th>
                            <th field="inv_post" width="50" sortable="true">Posting</th>       
                            <th field="action_button" width="50" sortable="true">action</th>       
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
                singleSelect:true,
                rownumbers:true,
                loadMsg:'',
                height:'auto',
                columns:[[
                    {field:'costd_name',title:'Cost Item'},
                    {field:'invdt_note',title:'Note'},
                    {field:'meter_start',title:'Start'},
                    {field:'meter_end',title:'End'},
                    {field:'meter_used',title:'Consumption'},
                    {field:'invdt_amount',title:'Amount'}
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
        },
        onLoadSuccess:function(target){
            print_window();
        }
    });
    // $('#dg').datagrid('enableFilter');

    var query;
    $('#search').submit(function(e){
        e.preventDefault();
        query = $(this).find('input[name=q]').val();
        if(query!=''){
            // refresh page
            $('#dg').datagrid('load', {
                q: query,
            });
            $('#dg').datagrid('reload');
        }
    });

    var print_window = function(){
        $('.print-window').off('click');
        $('.print-window').click(function(){
            var self = $(this); 
            var url = self.attr('href');
            var title = self.attr('title');
            var w = self.attr('data-width');
            var h = self.attr('data-height');
            
             // Fixes dual-screen position                         Most browsers      Firefox
            var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
            var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;

            var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
            var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

            var left = ((width / 2) - (w / 2)) + dualScreenLeft;
            var top = ((height / 2) - (h / 2)) + dualScreenTop;
            var newWindow = window.open(url, title, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

            // Puts focus on the newWindow
            if (window.focus) {
                newWindow.focus();
            }

            return false;
        });
    };
});        
</script>
<script src="{{asset('js/jeasycrud.js')}}"></script>
@endsection
