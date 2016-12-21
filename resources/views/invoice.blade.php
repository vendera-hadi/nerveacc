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
                    <div class="col-sm-3">
                        <input type="text" class="form-control" name="q" placeholder="Search Invoice No or Contract or Tenan Name">
                    </div>
                    <div class="col-sm-2">
                        <select class="form-control" name="inv_type">
                            <option value="">-- tipe invoice --</option>
                            @foreach($inv_type as $itype)
                            <option value="{{$itype->id}}" @if(Request::get('inv_type') == $itype->id){{'selected="selected"'}}@endif>{{$itype->invtp_name}}</option>
                            @endforeach
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
                    <div class="col-sm-3">
                        <button class="btn btn-info">Cari</button>
                    </div>
                </div>
                </form>

                <!-- template tabel -->
          		<table id="dg" title="List Invoice" class="easyui-datagrid" style="width:100%;height:380px" toolbar="#toolbar">
                    <!-- kolom -->
                    <thead>
                        <tr>
                            <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                            <th field="inv_number" width="100" sortable="true">No.Invoice</th>
                            <th field="contr_no" width="100" sortable="true">No Kontrak</th>
                            <th field="tenan_name" width="100" sortable="true">Nama Tenan</th>
                            <th field="unit" width="150" sortable="true">Unit</th>  
                            <th field="inv_date" width="100" sortable="true">Tgl Invoice</th>
                            <th field="inv_duedate" width="100" sortable="true">Jatuh Tempo</th>
                            <th field="inv_amount" width="100" sortable="true" align="right">Amount</th>
                            <th field="inv_outstanding" width="150" sortable="true" align="right">Outstanding Amount</th> 
                            <th field="invtp_name" width="150" sortable="true">Jenis Invoice</th>
                            <th field="inv_post" width="50" sortable="true">Posted</th>       
                        <th field="action_button" width="80" sortable="true">action</th>
                        </tr>
                    </thead>
                </table>
                <!-- end table -->
                
                <!-- icon2 atas table -->
                <div id="toolbar" class="datagrid-toolbar">
                    <a href="javascript:void(0)" class="easyui-linkbutton l-btn l-btn-small l-btn-plain" plain="true" onclick="addInv()" group="" id=""><span class="l-btn-text"><i class="fa fa-plus"></i>&nbsp;Create Invoice</span></a>                    
                    <a href="javascript:void(0)" class="easyui-linkbutton l-btn l-btn-small l-btn-plain" plain="true" onclick="postingInv()" group="" id=""><span class="l-btn-text"><i class="fa fa-check"></i>&nbsp;Posting Invoice</span></a>
                    <a href="javascript:void(0)" class="easyui-linkbutton l-btn l-btn-small l-btn-plain" plain="true" onclick="cancelInv()" group="" id=""><span class="l-btn-text"><i class="fa fa-ban"></i>&nbsp;Cancel Invoice</span></a>           
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
                                            <label>Invoice Number (No. Faktur)</label>
                                            <input type="text" class="form-control" name="inv_number" placeholder="No. Faktur" required>
                                        </div>
                                    </div>
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
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Invoice Type</label>
                                            <select name="invtp_id" class="form-control" required>
                                            @foreach($inv_type as $invtp)
                                                <option value="{{$invtp->id}}">{{$invtp->invtp_name}}</option>
                                            @endforeach
                                            </select>
                                        </div>
                                    </div>
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
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Contract</label>
                                            <div class="input-group">
                                                <input type="hidden" name="contr_id" id="txtContrId" required>
                                                <input type="text" class="form-control" id="txtContr" disabled>
                                                <span class="input-group-btn">
                                                    <button class="btn btn-info" type="button" id="chooseContractButton">Choose Contract</button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label>Add Cost Item</label>
                                            <select id="selectCostItemEdit" class="form-control">
                                                <?php $tempGroup = ''; ?>
                                                @foreach($cost_items as $key => $citm)
                                                  @if($citm->cost_name != $tempGroup && $key > 0){!!'</optgroup>'!!}@endif
                                                  @if($citm->cost_name != $tempGroup){!!'<optgroup label="'.$citm->cost_name.' ('.$citm->cost_code.')">'!!}@endif
                                                  <option value="{{$citm->id}}">{{$citm->costd_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button type="button" id="clickCostItemEdit">Add Cost Item</button>
                                    </div>
                                </div>

                                <div class="row" style="margin-top:40px">
                                    <div class="col-sm-12">
                                        <table id="tableCost" width="100%" class="table table-bordered" >
                                            <tr class="text-center">
                                              <td>Cost Item</td>
                                              <td>Name</td>
                                              <td>Unit</td>
                                              <td>Cost Rate</td>
                                              <td>Cost Burden</td>
                                              <td>Cost Admin</td>
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
<script type="text/javascript">
var entity = "List Invoice"; // nama si tabel, ditampilin di dialog
var get_url = "{{route('invoice.get')}}";
var get_url2 = "{{route('invoice.getdetail')}}";

$('.datepicker').datepicker({
            autoclose: true
        });

function postingInv(){
    var row = $('#dg').datagrid('getSelected');
    // console.log(row);
    if(row.inv_post == 'no'){
        $.messager.confirm('Confirm','Are you sure you want to post this Invoice ?',function(r){
            if (r){
                // posting invoice
                $.post('{{route('invoice.posting')}}',{id:row.id},function(result){
                    console.log(result);
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

$(function(){
    $('#dg').datagrid({
        view: detailview,
        url: get_url,
        pagination: true,
        pageSize:50,
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
        },
        rowStyler:function(index,row){
            // penanda dicancel
            if(row.inv_iscancel){
                return 'background-color:gray';
            }
        }
    });
    // $('#dg').datagrid('enableFilter');

    var query, invtype, from, to;
    $('#search').submit(function(e){
        e.preventDefault();
        query = $(this).find('input[name=q]').val();
        invtype = $(this).find('select[name=inv_type]').val();
        datefrom = $(this).find('input[name=date_from]').val();
        dateto = $(this).find('input[name=date_to]').val();

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
                dateto: dateto
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

    // paging
    $(document).delegate('.pagination li a','click',function(e){
        e.preventDefault();
        currenturl = $(this).attr('href');
        $.post(currenturl, null, function(data){
            $('#contractModalContent').html(data);
        });
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
        var contractname = $('input[name="contract"]:checked').data('name');
        $('input[name=contr_id]').val(contractid);
        $('#txtContr').val(contractname);
        $('#contractModalContent').text('');
        $('#contractModal').modal("hide");
    });

    $('#clickCostItemEdit').click(function(){
          var flag = false;
          var subtotal = 0;
          costItem = $('#selectCostItemEdit').val();
          $('.costdid').each(function(){
              console.log($(this).val());
              if($(this).val() == costItem){ 
                $.messager.alert('Warning', "Cost Item already exist in the list below");
                flag = true;
              }
          });
          if(!flag){
            costItemName = $('#selectCostItemEdit option:selected').parent().attr('label');
            $.post('{{route('cost_item.getDetail')}}', {id: costItem}, function(result){
                subtotal = parseFloat(result.costd_rate) + parseFloat(result.costd_burden) + parseFloat(result.costd_admin);
                $('#tableCost').append('<tr class="text-center"><input type="hidden" name="costd_id[]" class="costdid" value="'+result.id+'" data-total="'+subtotal+'"><input type="hidden" name="costd_amount[]" value="'+subtotal+'"><input type="hidden" name="costd_name[]" value="'+result.costitem.cost_name+'"><td>'+result.costitem.cost_name+'</td><td>'+result.costd_name+'</td><td>-</td><td>Rp. '+result.costd_rate+'</td><td>Rp. '+result.costd_burden+'</td><td>Rp. '+result.costd_admin+'</td><td><a href="#" class="removeCost"><i class="fa fa-times text-danger"></i></a></td></tr>');              
                updateTotal();
            });
          }
            // $('#editTableCost').append('<tr class="text-center"><input type="hidden" name="contr_id[]" value="'+contractID+'"><input type="hidden" name="cost_id[]" value="'+costItem+'"><td>'+costItemName+'</td><td><strong>Name :</strong> <input type="text" name="costd_name[]" class="form-control costd_name"  required><strong>Unit :</strong> <input type="text" name="costd_unit[]" class="form-control costd_unit" required><strong>Cost Rate :</strong> <input type="text" name="costd_rate[]" class="form-control costd_rate" required><strong>Cost Burden :</strong> <input type="text" name="costd_burden[]" class="form-control costd_burden" required><strong>Cost Admin :</strong> <input type="text" name="costd_admin[]" class="form-control costd_admin" required><strong>Invoice Type :</strong> <select name="inv_type[]" class="form-control">'+invoiceTypes+'</select><strong>Use Meter :</strong> <select name="is_meter[]" class="form-control"><option value="1">yes</option><option value="0">no</option></select></td><td><a href="#" class="removeCost"><i class="fa fa-times text-danger"></i></a></td></tr>');
    });

    $('#formAddInv').submit(function(e){
        e.preventDefault();
        if($('#txtContrId').val() == ""){ 
            $.messager.alert('Warning','Contract is required');
            return false;
        }
        if($('.costdid').length < 1){ 
            $.messager.alert('Warning','Cost item is required');
            return false;
        }
        $.post('{{route('invoice.insert')}}', $(this).serialize(), function(result){
            console.log(result);
            if(result.error) $.messager.alert('Warning',result.message);
            if(result.success){
                $.messager.alert('Success',result.message);
                $('#addInvModal').modal("hide");
                $('#dg').datagrid('reload');
            }
        },'json');
    });

    function updateTotal(){
        var total = 0;
        $('.costdid').each(function(){
            total = total + $(this).data('total');
        });
        // console.log(total);
        $('input[name=amount]').val(total);
        total = total.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
        $('#totalInv').html("Rp. "+total);
    }

    $(document).delegate('.removeCost','click',function(){
            if(confirm('Are you sure want to remove this cost item?')){
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
<!-- datepicker -->
<script type="text/javascript" src="{{ asset('plugins/datepicker/bootstrap-datepicker.js') }}"></script>
@endsection
