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
    <!-- datepicker -->
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/datepicker/datepicker3.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css') }}">
    <style type="text/css">
        .loadingScreen{
            position: absolute;
            width: 100%;
            height: 100%;
            background: black;
            z-index: 100;
            background: rgba(204, 204, 204, 0.5);
        }
    </style>
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
                <div class="loadingScreen" style="display:none">
                    <h3 style="line-height: 400px; text-align: center;">LOADING</h3>
                </div>
          		<!-- content -->
                <form id="search">
                <div class="row" style="margin-bottom:20px">
                    <div class="col-sm-3">
                        <input type="text" class="form-control" name="q" placeholder="Search Invoice No or Billing Info or Tenant Name">
                    </div>
                    <div class="col-sm-2">
                        <select class="form-control" name="inv_type">
                            <option value="">-- tipe invoice --</option>
                            @foreach($inv_type as $itype)
                            <option value="{{$itype->id}}" @if(Request::get('inv_type') == $itype->id){{'selected="selected"'}}@endif>{{$itype->invtp_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-3">
                        <select class="form-control" name="outstanding">
                            <option value="">-- PAID & NOT PAID --</option>
                            <option value="1" @if(Request::get('inv_type')==1){{'selected="selected"'}}@endif>NOT PAID ONLY</option>
                            <option value="2" @if(Request::get('inv_type')==2){{'selected="selected"'}}@endif>PAID ONLY</option>
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <div class="input-group date">
                          <div class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                          </div>
                          <input type="text" id="startDate" name="date_from" placeholder="From" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="input-group date">
                          <div class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                          </div>
                          <input type="text" id="startDate" name="date_to" placeholder="To" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd">
                        </div>
                    </div>
                    <div class="col-sm-offset-9 col-sm-3" style="margin-top:10px">
                        <button class="btn btn-info pull-right">Cari</button>
                    </div>
                </div>
                </form>

                <!-- template tabel -->
          		<table id="dg" title="List Invoice" class="easyui-datagrid" style="width:100%;height:380px" toolbar="#toolbar">
                    <!-- kolom -->
                    <thead>
                        <tr>
                            <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                            <th field="checkbox" width="25" sortable="true"></th>
                            <th field="inv_number" width="70" sortable="true">No.Invoice</th>
                            <!-- <th field="contr_no" width="100" sortable="true">No Kontrak</th> -->
                            <th field="tenan_name" width="100" sortable="true">Nama Tenant</th>
                            <th field="unit" width="45" sortable="true">Unit</th>  
                            <th field="inv_date" width="80" sortable="true">Tgl Invoice</th>
                            <th field="inv_duedate" width="80" sortable="true">Jatuh Tempo</th>
                            <th field="inv_amount" width="70" sortable="true" align="right">Amount</th>
                            <!-- <th field="inv_outstanding" width="150" sortable="true" align="right">Outstanding Amount</th>  -->
                            <th field="invtp_name" width="90" sortable="true">Jenis Invoice</th>
                            <th field="inv_post" width="40" sortable="true">Posted</th>       
                        <th field="action_button" width="80" sortable="true">action</th>
                        </tr>
                    </thead>
                </table>
                <!-- end table -->
                
                <!-- icon2 atas table -->
                <div id="toolbar" class="datagrid-toolbar">
                    <label style="margin-left:10px; margin-right:5px"><input type="checkbox" name="checkall" style="vertical-align: top;margin-right: 6px;"><span style="vertical-align: middle; font-weight:400">Check All</span></label>
                    @if(Session::get('role')==1 || in_array(60,Session::get('permissions')))
                    <a href="javascript:void(0)" class="easyui-linkbutton l-btn l-btn-small l-btn-plain" plain="true" onclick="addInv()" group="" id=""><span class="l-btn-text"><i class="fa fa-plus"></i>&nbsp;Invoice Lain Lain</span></a>                    
                    @endif
                    @if(Session::get('role')==1 || in_array(61,Session::get('permissions')))
                    <a href="javascript:void(0)" class="easyui-linkbutton l-btn l-btn-small l-btn-plain" plain="true" onclick="postingInv()" group="" id=""><span class="l-btn-text"><i class="fa fa-check"></i>&nbsp;Posting Invoice</span></a>
                    @endif
                    @if(Session::get('role')==1 || in_array(62,Session::get('permissions')))
                    <a href="javascript:void(0)" class="easyui-linkbutton l-btn l-btn-small l-btn-plain" plain="true" onclick="cancelInv()" group="" id=""><span class="l-btn-text"><i class="fa fa-ban"></i>&nbsp;Cancel Invoice</span></a>           
                    @endif
                    <a href="javascript:void(0)" class="easyui-linkbutton l-btn l-btn-small l-btn-plain" plain="true" onclick="printInv()" group="" id=""><span class="l-btn-text"><i class="fa fa-print"></i>&nbsp;Print</span></a>
                    <a href="javascript:void(0)" class="easyui-linkbutton l-btn l-btn-small l-btn-plain" plain="true" onclick="editFooter()" group="" id=""><span class="l-btn-text"><i class="fa fa-font"></i>&nbsp;Edit Footer/Label</span></a>
                </div>
                <!-- end icon -->
            
                <!-- hidden form buat create edit -->
                <div id="dlg" class="easyui-dialog" style="width:60%"
                        closed="true" buttons="#dlg-buttons">
                    <form id="fm" method="post" novalidate style="margin:0;padding:20px 50px">
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
                            <input id="cc" class="easyui-combobox" required="true" name="cost_id" style="width:100%" label="Component Billing:" data-options="valueField:'id',textField:'text',url:'{{route('cost_detail.options')}}'">
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

                <!-- Modal extra -->
                <div id="addInvModal" class="modal fade" role="dialog">
                  <div class="modal-dialog" style="width:900px">

                    <!-- Modal content-->
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Add Invoice</h4>
                      </div>
                      <div class="modal-body" id="addInvModalContent">
                            <!-- isi form -->
                            <form method="POST" id="formAddInv">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Invoice Date</label>
                                            <div class="input-group date">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-calendar"></i>
                                                </div>
                                                <input type="text" class="form-control datepicker" name="inv_date" placeholder="Invoice Date" data-date-format="yyyy-mm-dd" required>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="invtp_id" value="3">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Invoice Due Date</label>
                                            <div class="input-group date">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-calendar"></i>
                                                </div>
                                                <input type="text" class="form-control datepicker" name="inv_duedate" placeholder="Invoice Due Date" data-date-format="yyyy-mm-dd" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xs-6">
                                        <div class="form-group">
                                            <label>Tenant</label>
                                            <div class="input-group">
                                                <input type="hidden" name="contr_id" id="txtContrId" required>
                                                <input type="text" class="form-control" id="txtContr" disabled>
                                                <span class="input-group-btn">
                                                    <button class="btn btn-info" type="button" id="chooseContractButton">Choose Billing Info</button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                
                                <div class="row">
                                    <div class="col-sm-offset-8 col-sm-4">
                                        <button class="pull-right" type="button" id="clickCostItemEdit">Tambah Detail Biaya</button>
                                    </div>
                                </div>

                                <div class="row" style="margin-top:40px">
                                    <div class="col-sm-12">
                                        <table id="tableCost" width="100%" class="table table-bordered" >
                                            <tr class="text-center">
                                              <td width="220">COA</td>
                                              <td>Description</td>
                                              <td width="150">Amount (IDR)</td>
                                              <td></td>
                                            </tr>

                                            <tr class="text-center">
                                                <td id="coalist">
                                                    <select class="form-control" name="coa_code[]">
                                                        @foreach($coa as $code)
                                                            <option value="{{$code->coa_code}}">{{ trim($code->coa_code).' - '.trim($code->coa_name) }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td><input type="text" class="form-control" name="invdt_note[]" required></td>
                                                <td><input type="number" class="form-control amount" name="invdt_amount[]" value="0" required></td>
                                                <td></td>
                                            </tr>
                                            
                                          </table>
                                            <table width="50%">
                                                <input type="hidden" name="amount">
                                                <tr>
                                                    <td><b>Total</b></td>
                                                    <td id="totalInv">0</td>
                                                </tr>
                                            </table>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3 col-sm-offset-9">
                                        <button class="btn btn-info pull-right">Submit</button>
                                    </div>
                                </div>
                                
                            </form>
                            <!-- end form -->
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                      </div>
                    </div>

                  </div>
                </div>

                <!-- End Modal -->

                <div id="editFooterModal" class="modal fade" role="dialog">
                  <div class="modal-dialog" style="width:900px">

                    <!-- Modal content-->
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Edit Footer & Label Invoice</h4>
                      </div>
                      <div class="modal-body" id="editFooterModalContent">
                            <!-- isi form -->
                            <form method="POST" id="formEditFooter">
                                <input type="hidden" name="id" value="">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                          <label>Footer Invoice Text</label>
                                          <div id="footer_invoice">
                                          <textarea class="textarea" name="footer_invoice" class="form-control" style="width: 100%;" required></textarea>
                                          </div>
                                        </div>

                                        <div class="form-group">
                                          <label>Footer Invoice Label</label>
                                          <div id="footer_label_inv">
                                          <textarea class="textarea" name="footer_label_inv" class='form-control' style="width: 100%;" required></textarea>
                                          </div>
                                        </div>
                                    </div>
                                    
                                </div>
                                
                                <div class="row">
                                    <div class="col-sm-3 col-sm-offset-9">
                                        <button class="btn btn-info pull-right">Submit</button>
                                    </div>
                                </div>
                                
                            </form>
                            <!-- end form -->
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
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


                <!-- Modal select contract -->
                <div id="contractModal" class="modal fade" role="dialog">
                  <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                      
                      <div class="modal-body" id="contractModalContent">
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                      </div>
                    </div>

                  </div>
                </div>
                <!-- End Modal -->

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
<!-- datepicker -->
<script type="text/javascript" src="{{ asset('plugins/datepicker/bootstrap-datepicker.js') }}"></script>
<script type="text/javascript" src="{{asset('plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js')}}"></script>
<script type="text/javascript">
var entity = "List Invoice"; // nama si tabel, ditampilin di dialog
var get_url = "{{route('invoice.get')}}";
var get_url2 = "{{route('invoice.getdetail')}}";
// $(".textarea").wysihtml5();
$('.datepicker').datepicker({
            autoclose: true
        });

function postingInv(){
    // var row = $('#dg').datagrid('getSelected');
    var ids = [];
    $('input[name=check]:checked').each(function() {
       if($(this).data('posting') == "") ids.push($(this).val());
    });
    // console.log(row);
    // if(row.inv_post == 'no'){
    if(ids.length > 0){
        $.messager.confirm('Confirm','Are you sure you want to post this '+ids.length+' unposted Invoice ?',function(r){
            if (r){
                $('.loadingScreen').show();
                // posting invoice
                $.post('{{route('invoice.posting')}}',{id:ids},function(result){
                    console.log(result);
                    $('.loadingScreen').hide();
                    if(result.error){
                        $.messager.alert('Warning',result.message);
                    }
                    if(result.success){
                        $.messager.alert('Success',result.message);
                        $('#dg').datagrid('reload');
                    }
                },'json');
            }
        });
    }
    // }else{
    //     $.messager.alert('Warning', 'You can\'t post invoice that already posted');
    // }
}

function addInv(){
    var row = $('#dg').datagrid('getSelected');
    $('#addInvModal').modal("show");
}

function cancelInv(){
    var row = $('#dg').datagrid('getSelected');
    if(row.inv_post == 'no'){
        $.messager.confirm('Confirm','Are you sure you want to post this Invoice ?',function(r){
            if (r){
                $.post('{{route('invoice.cancel')}}',{id:row.id},function(result){
                    if(result.error){
                        $.messager.alert('Warning',result.message);
                    }
                    if(result.success){
                        $.messager.alert('Success',result.message);
                        $('#dg').datagrid('reload');
                    }
                },'json');
            }
        });
    }else{
        $.messager.alert('Warning', 'You can\'t post invoice that already posted');
    }
}

function editFooter(){
    var row = $('#dg').datagrid('getSelected');
    $('#footer_invoice').html('<textarea class="textarea" name="footer_invoice" class="form-control" style="width: 100%;" required></textarea>');
    $('#footer_label_inv').html('<textarea class="textarea" name="footer_label_inv" class="form-control" style="width: 100%;" required></textarea>');
    $.post('{{route('invoice.ajaxgetfooter')}}', {id: row.id}, function(data){
        console.log(data);
        if(data.errMsg){ 
            $.messager.alert('Warning',data.errMsg);
        }else{
            $('#formEditFooter input[name=id]').val(row.id);
            $('#formEditFooter textarea[name=footer_invoice]').val(data.result.footer);
            $('#formEditFooter textarea[name=footer_label_inv]').val(data.result.label);
            $(".textarea").wysihtml5();
            $('#editFooterModal').modal("show");
        }
    }, 'json');
}

function openWindow(url, title, w, h){
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
}

function printInv(){
    var ids = [];
    var url = '{{url('invoice/print_faktur')}}?';
    var title = 'Print Invoices';
    $('input[name=check]:checked').each(function() {
       ids.push($(this).val());
    });
    if(ids.length > 0){
        ids = {id: ids};
        url += $.param(ids);
        openWindow(url, title, 640, 660);
    }
}

$('input[name=checkall]').change(function() {
        if($(this).is(':checked')){ 
            $('input[name=check]').each(function(){
                $(this).prop('checked',true);
            });
        }else{
            $('input[name=check]').each(function(){
                $(this).prop('checked',false);
            });
        }
     });

$(function(){
    $('#dg').datagrid({
        view: detailview,
        url: get_url,
        pagination: true,
        pageSize:100,
        remoteFilter: true,
        rownumbers: true,
        singleSelect: true,
        fitColumns: true,
        pageList: [100,500,1000],
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
                    {field:'costd_name',title:'Component Billing'},
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
        },
        rowStyler:function(index,row){
            // penanda dicancel
            if(row.inv_iscancel){
                return 'background-color:gray';
            }
        }
    });
    // $('#dg').datagrid('enableFilter');

    var query, invtype, from, to, outstanding;
    $('#search').submit(function(e){
        e.preventDefault();
        query = $(this).find('input[name=q]').val();
        invtype = $(this).find('select[name=inv_type]').val();
        datefrom = $(this).find('input[name=date_from]').val();
        dateto = $(this).find('input[name=date_to]').val();
        outstanding = $(this).find('select[name=outstanding]').val();

        if((datefrom == "" && dateto !="") || (datefrom!="" && dateto=="")){
            alert('Isi kedua tanggal From dan To untuk melakukan filter tanggal');
        }else{

            if(datefrom!="" && dateto!=""){
                var dateFirst = datefrom.split('-');
                var dateSecond = dateto.split('-');
                var from = new Date(dateFirst[2], dateFirst[1], dateFirst[0]); //Year, Month, Date
                var to = new Date(dateSecond[2], dateSecond[1], dateSecond[0]);

                if(to<=from){
                    alert('Tanggal To harus lebih lama dari Tanggal From');
                    return false;
                }
            }

        // if(query!=''){
            // refresh page
            $('#dg').datagrid('load', {
                q: query,
                invtype: invtype,
                datefrom: datefrom,
                dateto: dateto,
                outstanding: outstanding
            });
            $('#dg').datagrid('reload');
        // }
        }
    });

    var currenturl; 
    $('#chooseContractButton').click(function(){
        $('#contractModal').modal("show");
        currenturl = '{{route('contract.popup')}}';
        $.post(currenturl, null, function(data){
            $('#contractModalContent').html(data);
        });
    });

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
    // $(document).delegate('.pagination li a','click',function(e){
    //     e.preventDefault();
    //     currenturl = $(this).attr('href');
    //     $.post(currenturl, null, function(data){
    //         $('#tenanModalContent').html(data);
    //     });
    // });

    // paging
    $(document).delegate('.pagination li a','click',function(e){
        e.preventDefault();
        currenturl = $(this).attr('href');
        $.post(currenturl, null, function(data){
            $('#contractModalContent').html(data);
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
    });

    $(document).delegate('#searchContract','submit',function(e){
        e.preventDefault();
        var data = $('#searchContract').serialize();
        currenturl = '{{route('contract.popup')}}';
        $.post(currenturl, data, function(data){
            $('#contractModalContent').html(data);
        });
    });

     $(document).delegate('#chooseContract','click',function(e){
        e.preventDefault();
        var contractid = $('input[name="contract"]:checked').val();
        var contractname = $('input[name="contract"]:checked').data('tenant')+" - "+$('input[name="contract"]:checked').data('unit');
        $('input[name=contr_id]').val(contractid);
        $('#txtContr').val(contractname);
        $('#contractModalContent').text('');
        $('#contractModal').modal("hide");
    });

    $('#clickCostItemEdit').click(function(){
          var flag = false;
          var subtotal = 0;
          var coa_clone = $('#coalist').html();
          $('#tableCost').append('<tr class="text-center"><td>'+coa_clone+'</td><td><input type="text" class="form-control" name="invdt_note[]" required></td><td><input type="number" class="form-control amount" name="invdt_amount[]" value="0" required></td><td><a class="deleteRow" style="cursor:pointer"><i class="fa fa-times"></i></a></td></tr>');
    });

    $(document).delegate('.amount','change',function(){
        if($(this).val() < 0) $(this).val(0);
        updateTotal(); 
    });

    $(document).delegate('.deleteRow','click',function(){
        $(this).parents('tr').remove();
        updateTotal(); 
    });

    $('#formEditFooter').submit(function(e){
        e.preventDefault();
        $.post('{{route('invoice.ajaxstorefooter')}}',$(this).serialize(),function(data){
            if(data.errMsg) $.messager.alert('Warning',data.errMsg);
            if(data.success){
                $.messager.alert('Success','Update success');
                $('#editFooterModal').modal("hide");
                $('#dg').datagrid('reload');
            }
        },'json');
    });

    $('#formAddInv').submit(function(e){
        e.preventDefault();
        $.post('{{route('invoice.insert')}}', $(this).serialize(), function(result){
            console.log(result);
            if(result.error) $.messager.alert('Warning',result.message);
            if(result.success){
                $.messager.alert('Success',result.message);
                window.open("{{url('invoice/receipt?id=')}}"+result.inv_id,null,"height=660,width=640,status=yes,toolbar=no,menubar=no,location=no");
                $('#addInvModal').modal("hide");
                $('#dg').datagrid('reload');
            }
        },'json');
    });

    function updateTotal(){
        var total = 0;
        $('.amount').each(function(){
            total = total + parseInt($(this).val());
        });
        console.log(total);
        // console.log(total);
        $('input[name=amount]').val(total);
        total = total.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
        $('#totalInv').html("Rp. "+total);
    }

    $(document).delegate('.removeCost','click',function(){
            if(confirm('Are you sure want to remove this component Billing?')){
                $(this).parent().parent().remove();
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
            
            openWindow(url, title, w, h);

            return false;
        });
    };

});        
</script>
<script src="{{asset('js/jeasycrud.js')}}"></script>
<!-- datepicker -->
<script type="text/javascript" src="{{ asset('plugins/datepicker/bootstrap-datepicker.js') }}"></script>
@endsection
