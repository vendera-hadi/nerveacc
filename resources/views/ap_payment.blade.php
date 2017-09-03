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
              <!-- kolom -->
              <thead>
                  <tr>
                      <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
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
              <a href="javascript:void(0)" class="easyui-linkbutton l-btn l-btn-small l-btn-plain" plain="true" onclick="postingInv()" group="" id=""><span class="l-btn-text"><i class="fa fa-check"></i>&nbsp;Posting Selected Payment</span></a>
              
          </div>
          <!-- end icon -->
          
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
        <div class="modal-body" id="detailModalContent">
        </div>
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
<!-- select2 -->
<script type="text/javascript" src="{{ asset('plugins/select2/select2.min.js') }}"></script>
<!-- datepicker -->
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
        var ids = [];
        $('input[name=check]:checked').each(function() {
           ids.push($(this).val());
        });
        if(ids.length > 0){
          $.messager.confirm('Confirm','Are you sure you want to post this '+ids.length+' Payment ?',function(r){
              if (r){
                  $('.loadingScreen').show();
                  $.post('{{route('treasury.posting')}}',{id:ids}, function(data){
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
</script>
@endsection