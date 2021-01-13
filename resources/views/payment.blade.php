@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Payment
@endsection

<!-- page title -->
@section('contentheader_title')
   Payment
@endsection

<!-- tambahan script atas -->
@section('htmlheader_scripts')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/default/easyui.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/icon.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/color.css') }}">
    <!-- select2 -->
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/select2/select2.min.css') }}">
    <!-- datepicker -->
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/datepicker/datepicker3.css') }}">
    <style>
    .datagrid-wrap{
        height: 400px;
    }
    .datepicker{z-index:999 !important;}

    .pagination > li > span{
        padding-bottom: 9px;
    }

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
        <li class="active">Payment</li>
    </ol>
@stop

@section('main-content')
<div class="row">
    <div class="col-md-12">
        <div class="loadingScreen" style="display:none">
            <h3 style="line-height: 400px; text-align: center;">LOADING</h3>
        </div>
        <!-- Tabs -->
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab_1" data-toggle="tab">Lists</a></li>
                @if(Session::get('role')==1 || in_array(69,Session::get('permissions')))
                <li><a href="#tab_2" data-toggle="tab">Add Payment</a></li>
                @endif
                <li class="hidden"><a href="#tab_3" data-toggle="tab">Edit Payment</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab_1">
                <!-- template tabel -->
                    <table id="dg" title="Payment" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
                        <!-- kolom -->
                        <thead>
                            <tr>
                                <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                                <th field="checkbox" width="20"></th>
                                <th field="tenan_name" sortable="true">Nama Tenant</th>
                                <th field="unit_code" sortable="true">No Unit</th>
                                <th field="inv_no" sortable="true">Invoice No</th>
                                <th field="paymtp_name" sortable="true">Pay.Type</th>
                                <th field="cashbk_name" sortable="true">Bank</th>
                                <th field="invpayh_note" width="80" sortable="true">Note</th>
                                <th field="invpayh_date" sortable="true">Pay.Date</th>
                                <th field="invpayh_amount" sortable="true">Total</th>
                                <th field="lebih_pembayaran" sortable="true">Lebih</th>
                                <th field="posting_at" sortable="true">Posting</th>
                                <th field="invpayh_post" sortable="true">Status</th>
                                <th field="action_button">Action</th>
                            </tr>
                        </thead>
                    </table>
                    <!-- end table -->
                    <!-- icon2 atas table -->
                    <div id="toolbar" class="datagrid-toolbar">
                        <label style="margin-left:10px; margin-right:5px"><input type="checkbox" name="checkall" style="vertical-align: top;margin-right: 6px;"><span style="vertical-align: middle; font-weight:400">Check All</span>
                        </label>
                        @if(Session::get('role')==1 || in_array(70,Session::get('permissions')))
                        <a href="javascript:void(0)" class="easyui-linkbutton l-btn l-btn-small l-btn-plain" plain="true" onclick="postingInv()" group="" id=""><span class="l-btn-text"><i class="fa fa-check"></i>&nbsp;Posting Selected Payment</span>
                        </a>
<<<<<<< Updated upstream
=======
                        <a href="javascript:void(0)" class="easyui-linkbutton l-btn l-btn-small l-btn-plain" plain="true" onclick="sendInv()" group="" id=""><span class="l-btn-text"><i class="fa fa-paper-plane"></i>&nbsp;Send Email Kwitansi</span>
                        </a>
                        <a href="javascript:void(0)" class="easyui-linkbutton l-btn l-btn-small l-btn-plain" plain="true" onclick="unpostingInv()" group="" id=""><span class="l-btn-text"><i class="fa fa-remove"></i>&nbsp;Unposting Selected Payment</span>
                        </a>
>>>>>>> Stashed changes
                        @endif
                    </div>
                    <div id="dlg" class="easyui-dialog" style="width:40%"
                        closed="true" buttons="#dlg-buttons">
                        <form id="fm" method="post" novalidate style="margin:0;padding:20px 50px">
                            <div style="margin-bottom:10px">
                                <input type="text" class="easyui-datebox" required="required" name="posting_date" label="Posting Date :" style="width:100%" data-options="formatter:myformatter,parser:myparser">
                            </div>
                            <div style="margin-bottom:10px">
                                <select id="sendflag" class="easyui-combobox" required="true" name="send_flag" label="Send Kwitansi:" style="width:300px;">
                                    <option value="1">No</option>
                                    <option value="2">Yes</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div id="dlg-buttons">
                        <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="savepayment()" style="width:90px">Save</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg').dialog('close')" style="width:90px">Cancel</a>
                    </div>

                    <!-- end icon -->
                </div>
                <!-- /.tab-pane -->
                <div class="tab-pane" id="tab_2">
                    <div id="contractStep1">
                        <form method="POST" id="formPayment">
                            <div class="form-group">
                                <label>Unit Code</label>
                                <select class="form-control contrId choose-contract" name="tenan_id" style="width:100%"></select>
                            </div>
                            <div class="form-group">
                                <label>No Giro</label>
                                <input type="text" name="invpayh_checkno" class="form-control">
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Bank</label>
                                        <select class="form-control cashbkId choose-style" name="cashbk_id" style="width:100%">
                                            <option value="">-</option>
                                            <?php foreach ($cashbank_data as $key => $value) { ?>
                                            <option value="<?php echo $value['id']?>"><?php echo $value['cashbk_name']?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Payment Type</label>
                                        <select class="form-control paymtpCode choose-style" name="paymtp_code" style="width:100%">
                                            <option value="">-</option>
                                            <?php foreach ($payment_type_data as $key => $value) { ?>
                                            <option value="<?php echo $value['id']?>"><?php echo $value['paymtp_name']?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Payment Date</label>
                                        <div class="input-group date">
                                            <div class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </div>
                                            <input type="text" id="invpayhDate" name="invpayh_date" required="required" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Cheque/Giro Date</label>
                                        <div class="input-group date">
                                            <div class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </div>
                                            <input type="text" id="invpayhGiro" name="invpayh_giro" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Note</label>
                                <input type="text" name="invpayh_note" class="form-control">
                            </div>
                            <div class="ajax-detail"></div>
                            <?php 
                            session_start();
                            $secret=md5(uniqid(rand(), true));
                            Session::set('FORM_SECRET', $secret); 
                            ?>
                            <input type="hidden" id="form_secret" name="form_secret" value="<?php echo $secret; ?>">
                            <button type="submit" id="submitForm" class="btn btn-primary">submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- content -->

        <!-- Modal extra -->
        <div id="detailModal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Payment Information</h4>
                    </div>
                    <div class="modal-body" id="detailModalContent"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Modal -->

        <!-- Modal select unit -->
        <div id="unitModal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-body" id="unitModalContent"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Modal -->

        <!-- modal form -->
        <div id="editModal" class="modal fade" role="dialog">
            <div class="modal-dialog" style="width:80%">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Edit Payment</h4>
                    </div>
                    <div class="modal-body"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- end modal form -->

      <!-- content -->
    </div>
</div>

@endsection

@section('footer-scripts')
<script src="{{asset('plugins/jQueryUI/jquery-ui.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('plugins/jquery-easyui/jquery.easyui.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/datagrid-filter.js') }}"></script>
<script type="text/javascript" src="{{ asset('plugins/select2/select2.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('plugins/datepicker/bootstrap-datepicker.js') }}"></script>

<script type="text/javascript">
    var entity = "Payment"; // nama si tabel, ditampilin di dialog
    var get_url = "{{route('payment.get')}}";

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
            onLoadSuccess:function(target){
                print_window();
            }
        });
        dg.datagrid('enableFilter');
    });

    $('#formPayment').submit(function(e){
        e.preventDefault();
        $('#submitForm').attr('disabled','disabled');
        var startdate = $('#invpayhDate').val();

        if(startdate == ''){
            $.messager.alert('Warning','Payment date must be choose');
        }else if($('#contrId').val() == ""){
          $.messager.alert('Warning','Payment must be choose');
        }else if($('#cashbkId').val() == ""){
          $.messager.alert('Warning','Bank must be choose');
        }else if($('#paymtpCode').val() == ""){
          $.messager.alert('Warning','Payment type must be choose');
        }else{
          var allFormData = $('#formPayment').serialize();
          var i;
          $.post('{{route('payment.insert')}}',allFormData, function(result){
              $('#submitForm').removeAttr('disabled');
              alert(result.message);
              if(result.status == 1){
                for (i = 0; i < result.paym_id.length; i++) {
                  window.open("{{url('invoice/print_kwitansi?id=')}}"+result.paym_id[i],null,"height=660,width=640,status=yes,toolbar=no,menubar=no,location=no");
                }
                location.reload();
              }
          });

          return false;
        }
    });

    $('.datepicker').datepicker({
        autoclose: true
    });

    $(".choose-style").select2();

    $(".choose-contract").select2({
        ajax: {
            url: "{{route('contract.optParent')}}",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
        minimumInputLength: 0
    })

    $(".contrId").change(function(){
        var url = "{{route('payment.get_invoice')}}";
        var val = $(this).val();

        url = url+"?tenan_id="+val;

        $.ajax({
            url: url,
            type: 'GET',
            success: function(result) {
                $('.ajax-detail').html(result);

                return false;
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert('failed during proccess.');
                return false;
            }
        });
    });

    $(document).delegate('.getDetail','click',function(){
        $('#detailModalContent').html('<center><img src="{{ asset('img/loading.gif') }}"><p>Loading ...</p></center>');
        var id = $(this).data('id');
        $.post('{{route('payment.getdetail')}}',{id:id}, function(data){
            $('#detailModalContent').html(data);
        });
    });

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
    /*
    function postingInv(){
        var ids = [];
        $('input[name=check]:checked').each(function() {
           ids.push($(this).val());
        });
        if(ids.length > 0){
          $.messager.confirm('Confirm','Are you sure you want to post this '+ids.length+' Payment ?',function(r){
              if (r){
                  $('.loadingScreen').show();
                  $.post('{{route('payment.posting')}}',{id:ids}, function(data){
                      alert(data.message);
                      $('.loadingScreen').hide();
                      if(data.success == 1){
                        location.reload();
                      } else{
                        return false;
                      }
                  });
              }
          });
        }
    }
    */
    function postingInv(){
        $('#dlg').dialog('open').dialog('center').dialog('setTitle','Posting Payment');
        $('#fm').form('clear');
    }

    function savepayment(){
        var ids = [];
        var url = "{{route('payment.get_token')}}";
        var val = 10;
        url = url+"?type="+val;

        $('input[name=check]:checked').each(function() {
           ids.push($(this).val());
        });

        if(ids.length > 0){
            $.ajax({
                url: url,
                type: 'GET',
                success: function(token) {
                    var data = $("#fm").serializeArray();
                    data.push({name: "id", value: ids});
                    data.push({name: "token", value: token});
                    $.post('{{route('payment.posting')}}',data, function(result){
                        if(result.success == 1){
                            $.messager.alert('Success','Data saved');
                            $('#dlg').dialog('close');
                            $('#dg').datagrid('reload');
                        }else{
                            $.messager.show({
                                title: 'Error',
                                msg: result.message
                            });
                        }
                    });
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    alert('failed during proccess.');
                    return false;
                }
            });
        }
        
    }

    function unpostingInv(){
        var ids = [];
        $('input[name=check]:checked').each(function() {
           ids.push($(this).val());
        });
        if(ids.length > 0){
          $.messager.confirm('Confirm','Are you sure you want to unpost this '+ids.length+' Payment ?',function(r){
              if (r){
                  $('.loadingScreen').show();
                  $.post('{{route('payment.unposting')}}',{id:ids}, function(data){
                      alert(data.message);
                      $('.loadingScreen').hide();
                      if(data.success == 1){
                        $('#dg').datagrid('reload');
                      } else{
                        return false;
                      }
                  });
              }
          });
        }
    }

<<<<<<< Updated upstream
=======
    function sendInv(){
        var ids = [];
        $('input[name=check]:checked').each(function() {
           ids.push($(this).val());
        });
        if(ids.length > 0){
          $.messager.confirm('Confirm','Are you sure you want to send email this '+ids.length+' Payment ?',function(r){
              if (r){
                  $('.loadingScreen').show();
                  $.post('{{route('payment.sendkwitansi')}}',{id:ids}, function(data){
                      alert(data.message);
                      $('.loadingScreen').hide();
                      if(data.success == 1){
                        $('#dg').datagrid('reload');
                      } else{
                        return false;
                      }
                  });
              }
          });
        }
    }

    $(document).delegate('.void-confirm','click',function(){
        if(confirm("are you sure you want void this payment?")){
            var url = $(this).attr('href');
            $.ajax({
                url: url,
                type: 'GET',
                success: function(result) {
                    alert(result.message);
                    if(result.status == 1) $('#dg').datagrid('reload');
                    return false;
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    alert('failed during proccess.');
                    return false;
                }
            });
            return false;
        }else{
            return false;
        }
    });

    $(document).delegate('.posting-confirm','click',function(){
        if(confirm("are you sure you want posting this payment?")){
            var id = $(this).attr('data-id');

            $.post('{{route('payment.posting')}}',{id:id}, function(data){
                alert(data.message);
                if(data.success == 1){
                    $('#dg').datagrid('reload');
                }else{
                    return false;
                }
            });
        }else{
            return false;
        }
    });

    $(document).delegate('.paid-amount','change',function(){
        var maxVal = parseFloat($(this).attr('maxlength'));
        var currentVal = parseFloat($(this).val());
        if(currentVal > maxVal) $(this).val(maxVal);
        if(currentVal < 1) $(this).val(1);
      });

    $(document).delegate('.paid-check','change',function(){
        if($(this).is(':checked')) $(this).parents('tr').find('.paid-amount').removeAttr('disabled');
        else $(this).parents('tr').find('.paid-amount').attr('disabled','disabled');
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

>>>>>>> Stashed changes
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

    function myformatter(date){
        var y = date.getFullYear();
        var m = date.getMonth()+1;
        var d = date.getDate();
        return (d<10?('0'+d):d)+'-'+(m<10?('0'+m):m)+'-'+y;
    }

    function myparser(s){
        if (!s) return new Date();
        var ss = (s.split('-'));
        var y = parseInt(ss[0],10);
        var m = parseInt(ss[1],10);
        var d = parseInt(ss[2],10);
        if (!isNaN(y) && !isNaN(m) && !isNaN(d)){
            return new Date(d,m-1,y);
        } else {
            return new Date();
        }
    }

</script>
@endsection
