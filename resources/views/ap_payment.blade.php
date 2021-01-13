@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    AP Payment
@endsection

<!-- page title -->
@section('contentheader_title')
   AP Payment
@endsection

<!-- tambahan script atas -->
@section('htmlheader_scripts')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/default/easyui.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/icon.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/color.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/select2/select2.min.css') }}">
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
        <li class="active">AP Payment</li>
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
                <!-- nanti tambahin permission -->
                <li><a href="#tab_2" data-toggle="tab">Add Payment</a></li>
                <li class="hidden"><a href="#tab_3" data-toggle="tab">Edit Payment</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab_1">

                    @if(Session::get('error'))
                        <div class="alert alert-danger">
                            <strong>Error!</strong> {{ Session::get('error') }}
                        </div>
                    @endif

                    @if(Session::get('success'))
                        <div class="alert alert-success">
                            <strong>Success</strong> {{ Session::get('success') }}
                        </div>
                    @endif

                    @if (count($errors) > 0)
                        <div class="alert alert-danger">
                            <ul>
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- template tabel -->
                    <table id="dg" title="AP Payment" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
                        <thead>
                            <tr>
                                <th field="checkbox" width="25"></th>
                                <th field="payment_code" sortable="true">Payment Code</th>
                                <th field="spl_name" width="50" sortable="true">Supplier</th>
                                <th field="invoice_no" width="50" sortable="true">AP Invoice No</th>
                                <th field="amount" sortable="true">Total</th>
                                <th field="paymtp_name" width="50" sortable="true">Payment Type</th>
                                <th field="payment_date" width="50" sortable="true">Payment Date</th>
                                <th field="posting" width="50" sortable="true">Posting Status</th>
                                <th field="action_button">Action</th>
                            </tr>
                        </thead>
                    </table>
                    <!-- end table -->
      
                    <!-- icon2 atas table -->
                    <div id="toolbar" class="datagrid-toolbar">
                        <label style="margin-left:10px; margin-right:5px"><input type="checkbox" name="checkall" style="vertical-align: top;margin-right: 6px;"><span style="vertical-align: middle; font-weight:400">Check All</span></label>
                        <!-- tambahin permission -->
                        <a href="javascript:void(0)" class="easyui-linkbutton l-btn l-btn-small l-btn-plain" plain="true" onclick="postingInv()" group="" id=""><span class="l-btn-text"><i class="fa fa-check"></i>&nbsp;Posting Selected</span></a>
                        <a href="javascript:void(0)" class="easyui-linkbutton l-btn l-btn-small l-btn-plain" plain="true" onclick="unpostingInv()" group="" id=""><span class="l-btn-text"><i class="fa fa-remove"></i>&nbsp;Unposting Selected Payment</span>
                        </a>
                    </div>
                    <!-- end icon -->
                    <div id="dlg" class="easyui-dialog" style="width:40%"
                        closed="true" buttons="#dlg-buttons">
                        <form id="fm" method="post" novalidate style="margin:0;padding:20px 50px">
                            <div style="margin-bottom:10px">
                                <input type="text" class="easyui-datebox" required="required" name="posting_date" label="Posting Date :" style="width:100%" data-options="formatter:myformatter,parser:myparser">
                            </div>
                        </form>
                    </div>
                    <div id="dlg-buttons">
                        <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="savepayment()" style="width:90px">Save</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg').dialog('close')" style="width:90px">Cancel</a>
                    </div>
                </div>
                <!-- /.tab-pane -->
                <div class="tab-pane" id="tab_2">
                    <div id="contractStep1">
                        <form method="POST" action="{{route('treasury.insert')}}" id="formPayment">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Supplier</label>
                                        <select name="spl_id" id="supplier" class="form-control" required="">
                                        <option value="">-</option>
                                        @foreach($suppliers as $val)
                                        <option value="{{$val->id}}">{{$val->spl_name}}</option>
                                        @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Payment Date</label>
                                        <div class="input-group date">
                                            <div class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </div>
                                            <input type="text" name="payment_date" required="required" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>No Cek/Giro</label>
                                        <input type="text" name="check_no" class="form-control">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Cek/Giro Date</label>
                                        <div class="input-group date">
                                            <div class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </div>
                                            <input type="text" id="invpayhGiro" name="check_date" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Bank</label>
                                        <select class="form-control cashbkId choose-style" name="cashbk_id" style="width:100%">
                                        <option value="">-</option>
                                        @foreach ($cashbank_data as $key => $value)
                                        <option value="{{ $value->id }}">{{ $value->cashbk_name }}</option>
                                        @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="col-sm-6" style="padding-left:0px;">
                                        <div class="form-group">
                                            <label>Payment Type</label>
                                            <select class="form-control paymtpCode choose-style" name="paymtp_id" required="" style="width:100%">
                                            <option value="">-</option>
                                            @foreach ($payment_type_data as $key => $value)
                                            <option value="{{ $value->id }}">{{ $value->paymtp_name }}</option>
                                            @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6" style="padding-right:0px;">
                                        <div class="form-group">
                                            <label>Pajak</label>
                                            <select class="form-control ppncode choose-style" name="pajak_id" style="width:100%">
                                            <option value="">-</option>
                                            @foreach ($ppn_data as $key => $value)
                                            <option value="{{ $value->id }}" data-id="{{ $value->amount}}">{{ $value->name }}</option>
                                            @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Note</label>
                                <input type="text" name="note" class="form-control">
                            </div>

                            <div class="ajax-detail"></div>
            
                            <button type="submit" id="submitForm" class="btn btn-primary">submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- content -->
    </div>
</div>

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

@endsection

@section('footer-scripts')
<script src="{{asset('plugins/jQueryUI/jquery-ui.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('plugins/jquery-easyui/jquery.easyui.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/datagrid-filter.js') }}"></script>
<script type="text/javascript" src="{{ asset('plugins/select2/select2.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('plugins/datepicker/bootstrap-datepicker.js') }}"></script>

<script type="text/javascript">
	var entity = "AP Payment"; // nama si tabel, ditampilin di dialog
    var get_url = "{{route('treasury.get')}}";

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

    $(document).delegate('.getDetail','click',function(){
        $('#detailModalContent').html('<center><img src="{{ asset('img/loading.gif') }}"><p>Loading ...</p></center>');
        var id = $(this).data('id');
        $.post('{{route('treasury.getdetail')}}',{id:id}, function(data){
            $('#detailModalContent').html(data);
        });
    });

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

    $('.datepicker').datepicker({
        autoclose: true
    });

    $("#supplier").change(function(){
        var url = "{{route('treasury.getapsupplier')}}";
        var val = $(this).val();

        url = url+"?id="+val;

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

    // posting
    $(document).delegate('.posting-confirm','click',function(){
      if(confirm("are you sure you want posting this payment?")){
        var id = $(this).attr('data-id');

        $.post('{{route('treasury.posting')}}',{id:id}, function(data){
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

    function postingInv(){
        $('#dlg').dialog('open').dialog('center').dialog('setTitle','Posting Payment');
        $('#fm').form('clear');
    }

    function savepayment(){
        var ids = [];
        $('input[name=check]:checked').each(function() {
           ids.push($(this).val());
        });
        if(ids.length > 0){
            var data = $("#fm").serializeArray();
            data.push({name: "id", value: ids});
            $.post('{{route('treasury.posting')}}',data, function(result){
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
        }
    }

    function unpostingInv(){
        var ids = [];
        $('input[name=check]:checked').each(function() {
           ids.push($(this).val());
        });
        if(ids.length > 0){
            $.messager.confirm('Confirm','Are you sure you want to unpost this '+ids.length+' data ?',function(r){
                if (r){
                    $('.loadingScreen').show();
                    $.post('{{route('treasury.unposting')}}',{id:ids}, function(data){
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
      var url = '{{url('treasury/print_bpv')}}?';
      var title = 'Print BPV';
      $('input[name=check]:checked').each(function() {
         ids.push($(this).val());
      });
      if(ids.length > 0){
          ids = {id: ids};
          url += $.param(ids);
          openWindow(url, title, 640, 660);
      }
  }
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