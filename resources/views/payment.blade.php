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
    <div class="container spark-screen">
        <div class="row">
            <div class="col-md-11">

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
                            <th field="checkbox" width="25"></th>
                            <th field="tenan_name" sortable="true">Nama Tenant</th>
                            <th field="unit_code" width="50" sortable="true">No Unit</th>
                            <th field="inv_no" sortable="true">Invoice No</th>
                            <th field="paymtp_name" width="50" sortable="true">Payment Type</th>
                            <th field="invpayh_date" width="50" sortable="true">Payment Date</th>
                            <th field="invpayh_amount" sortable="true">Total</th>
                            
                            <th field="invpayh_post" width="50" sortable="true">Posting Status</th>
                            <th field="action_button">Action</th>
                        </tr>
                    </thead>
                </table>
                <!-- end table -->
                
                <!-- icon2 atas table -->
                <div id="toolbar" class="datagrid-toolbar">
                    <label style="margin-left:10px; margin-right:5px"><input type="checkbox" name="checkall" style="vertical-align: top;margin-right: 6px;"><span style="vertical-align: middle; font-weight:400">Check All</span></label>
                    @if(Session::get('role')==1 || in_array(70,Session::get('permissions')))
                    <a href="javascript:void(0)" class="easyui-linkbutton l-btn l-btn-small l-btn-plain" plain="true" onclick="postingInv()" group="" id=""><span class="l-btn-text"><i class="fa fa-check"></i>&nbsp;Posting Selected Payment</span></a>
                    @endif
                </div>
                <!-- end icon -->
                
              </div>
              <!-- /.tab-pane -->
              <div class="tab-pane" id="tab_2">
                <div id="contractStep1">
                  <form method="POST" id="formPayment">
                      
                      <div class="form-group">
                          <label>Tenant Name</label>
                          <select class="form-control contrId choose-contract" name="contr_id" style="width:100%">
                          </select>
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
                                <?php
                                    foreach ($cashbank_data as $key => $value) {
                                ?>
                                <option value="<?php echo $value['id']?>"><?php echo $value['cashbk_name']?></option>
                                <?php
                                    }
                                ?>
                              </select>
                          </div>
                        </div>
                        <div class="col-sm-6">
                          <div class="form-group">
                              <label>Payment Type</label>
                              <select class="form-control paymtpCode choose-style" name="paymtp_code" style="width:100%">
                                <option value="">-</option>
                                <?php
                                    foreach ($payment_type_data as $key => $value) {
                                ?>
                                <option value="<?php echo $value['id']?>"><?php echo $value['paymtp_name']?></option>
                                <?php
                                    }
                                ?>
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
                <div id="unitModal" class="modal fade" role="dialog">
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
                

                <!-- modal form -->
                <div id="editModal" class="modal fade" role="dialog">
                  <div class="modal-dialog" style="width:80%">

                    <!-- Modal content-->
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Edit Payment</h4>
                      </div>
                      <div class="modal-body">
                        
                      </div>
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
    </div>
@endsection

@section('footer-scripts')
<script src="{{asset('plugins/jQueryUI/jquery-ui.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('plugins/jquery-easyui/jquery.easyui.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/datagrid-filter.js') }}"></script>
<!-- select2 -->
<script type="text/javascript" src="{{ asset('plugins/select2/select2.min.js') }}"></script>
<!-- datepicker -->
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
          
          $.post('{{route('payment.insert')}}',allFormData, function(result){
              $('#submitForm').removeAttr('disabled');
              alert(result.message);
              if(result.status == 1){
                window.open("{{url('invoice/print_kwitansi?id=')}}"+result.paym_id,null,"height=660,width=640,status=yes,toolbar=no,menubar=no,location=no");
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
        });

    $(".contrId").change(function(){
        var url = "{{route('payment.get_invoice')}}";
        var val = $(this).val();

        url = url+"?contract_id="+val;

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

    $(document).delegate('.void-confirm','click',function(){
      if(confirm("are you sure you want void this payment?")){
        var url = $(this).attr('href');
        
        $.ajax({
            url: url,
            type: 'GET',
            success: function(result) {
              alert(result.message);
              if(result.status == 1) location.reload();

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
              location.reload();
            } else{
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
</script>
@endsection
