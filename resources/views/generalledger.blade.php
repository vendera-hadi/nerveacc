@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    General Ledger
@endsection

<!-- page title -->
@section('contentheader_title')
   General Ledger
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
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/daterangepicker/daterangepicker-bs3.css') }}">
    
    <style>
    .datagrid-wrap{
        height: 400px;
    }
    .datepicker{z-index:1151 !important;}

    .select2-container--default .select2-selection--single{
      border-radius: 0px;
      height: 36px;
    }
    </style>
@endsection

@section('contentheader_breadcrumbs')
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">General Ledger</li>
    </ol>
@stop

@section('main-content')
<div class="container spark-screen">
        <div class="row">
            <div class="col-md-11">

          <!-- Tabs -->
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#tab_1" data-toggle="tab">Lists</a></li>
              <li class="hidden"><a href="#tab_3">Edit Journal</a></li>
            </ul>

            <div class="tab-content">
              <div class="tab-pane active" id="tab_1">
                
<form id="formFilter" action="" method="GET">
                <div class="row">
                <div class="col-sm-4">
                    <!-- date range -->
                
                <div class="form-group">
                    <div class="input-group">
                      <div class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                      </div>
                      <input type="text" name="filterdate" class="form-control pull-right" placeholder="Filter by date" id="reservation">
                    </div>
                    <!-- /.input group -->
                  </div>
                  <!-- /.form group -->
                </div>

                <div class="col-sm-3">
                  <select name="dept" class="form-control">
                    <option value="">All Department</option> 
                    @foreach($departments as $dept)
                    <option value="{{$dept->id}}" @if(Request::input('dept') == $dept->id){{'selected="selected"'}}@endif>{{$dept->dept_name}}</option>
                    @endforeach
                  </select>
                </div>

                <div class="col-sm-3">
                    <select class="form-control" name="jour_type_id">
                      <option value="">All Journal Type</option>
                      @foreach($journal_types as $jourtype)
                      <option value="{{$jourtype->id}}" @if(Request::input('jour_type_id') == $jourtype->id){{'selected="selected"'}}@endif>{{$jourtype->jour_type_name}}</option>
                      @endforeach
                    </select>
                </div>

                <div class="col-sm-2">
                    <button type="submit" class="btn btn-success">Filter</button>
                </div>

              </div>

              <div class="row" style="margin-bottom:15px">
                  <div class="col-sm-3">
                  <select class="form-control js-example-basic-single" id="selectAccount" style="width:100%">
                              <option value="">Choose COA</option>
                              
                              @foreach($accounts as $key => $coa)
                                  <option value="{{$coa->coa_code}}" data-name="{{$coa->coa_name}}">{{$coa->coa_code." ".$coa->coa_name}}</option>
                              @endforeach
                            </select>
                </div>

                  <div class="col-sm-4">
                    <input class="form-control" type="text" name="q" placeholder="Keyword (Tenant Name or Description)">
                  </div>
              </div> 
            </form>

            @if(Request::input('filterdate'))
            <div class="row">
              <div class="col-xs-12">
                <h3>Searh Journal by : {{Request::input('filterdate')}}</h3>
              </div>
            </div>
            @endif

                  <!-- template tabel -->
                <table id="dg" title="General Ledger Entries" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
                    <!-- kolom -->
                    <thead>
                        <tr>
                            <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                            <th field="coa_code" width="120" sortable="true">Acc No</th>
                            <th field="coa_name" width="120" sortable="true">Acc Name</th>
                            <th field="ledg_date" width="120" sortable="true">Date</th>
                            <th field="ledg_refno" width="120" sortable="true">Invoice No</th>
                            <th field="ledg_description" width="120" sortable="true">Description</th>
                            <th field="debit" width="120" sortable="true">Debit</th>
                            <th field="credit" width="120" sortable="true">Credit</th>
                            <th field="jour_type_prefix" width="120" sortable="true">Jrnl Type</th>
                            <th field="tenan_name" width="120" sortable="true">Tenant Name</th>
                            <th field="action">Action</th>
                        </tr>
                    </thead>
                </table>
                <!-- end table -->
                
                
              </div>

              <!-- /.tab-pane -->
              <div class="tab-pane" id="tab_2">
                <!-- add journal -->
                <form method="POST" id="formJournal">
                  <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Date</label>
                            <div class="input-group date">
                              <div class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                              </div>
                              <input type="text" class="form-control pull-right" name="ledg_date" id="datepicker" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="form-group">
                        <label>Ref No</label>
                        <input type="text" class="form-control" name="ledg_refno" required>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                      

                      <div class="col-sm-6">
                        

                        <div class="form-group">
                          <label>Journal Type</label>
                          <select class="form-control" name="jour_type_id" required>
                            <option value="">Choose Journal Type</option>
                            @foreach($journal_types as $jourtype)
                            <option value="{{$jourtype->id}}">{{$jourtype->jour_type_name}}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>

                  </div>



                  <div class="AccountList" style="margin-top:30px">
                      <div class="row">
                        <div class="col-sm-6">
                            <select class="form-control js-example-basic-single" id="selectAccount" style="width:100%">
                              <option value="">Choose Account</option>
                              
                              @foreach($accounts as $key => $coa)
                                  <option value="{{$coa->coa_code}}" data-name="{{$coa->coa_name}}">{{$coa->coa_code." ".$coa->coa_name}}</option>
                              @endforeach
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <button type="button" id="addAccount" class="btn btn-default">Add Line</button>
                        </div>
                      </div>
                  </div>
                  <br><br>
                  <div class="row">
                      <div class="col-sm-12">
                          <table id="tableJournal" width="100%" class="table table-bordered">
                              <tr class="text-center">
                                <td>Account Code</td>
                                <td>Account Name</td>
                                <td>Description</td>
                                <td>Department</td>
                                <td>Debit/Credit</td>
                                <td>Value</td>
                                <td></td>
                              </tr>
                              
                              <tr id="rowEmpty">
                                <td colspan="7"><center>Data Kosong. Pilih account dan Add Line terlebih dulu</center></td>
                              </tr>
                            </table>

                            <table width="50%" class="table table-bordered">
                              <tr class="text-center">
                                <td>Status</td>
                                <td>Total Debit</td>
                                <td>Total Credit</td>
                              </tr>
                              <tr class="text-center">
                                <td id="ledgerStatus" style="font-weight:bold;"></td>
                                <td id="totalDebit" style="font-weight:bold; color:red">0</td>
                                <td id="totalCredit" style="font-weight:bold; color:blue">0</td>
                              </tr>
                            </table>
                      </div>
                  </div>
                  <br><br>
                  <div class="row">
                      <div class="col-sm-12">
                          <button type="submit" id="submitJournal">Submit</button>
                      </div>
                  </div>

                </form>
                <!-- add journal -->
              </div>
              

              <div class="tab-pane" id="tab_3">
              </div>

              </div>

            </div>
            <!-- /.tab-content -->
          </div>
          <!-- Tabs -->

          <!-- Modal extra -->
                <div id="detailModal" class="modal fade" role="dialog">
                  <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content" style="width:100%">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Journal Detail</h4>
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
<script type="text/javascript" src="{{ asset('plugins/daterangepicker/moment.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('plugins/daterangepicker/daterangepicker.js') }}"></script>

<script type="text/javascript">
$('#reservation').daterangepicker();
$(document).ready(function() {
  $(".js-example-basic-single").select2();
});

var entity = "Journal"; // nama si tabel, ditampilin di dialog
var get_url = "{!!route('genledger.get', ['date'=> Request::get('filterdate'), 'dept'=> Request::get('dept'), 'jour_type_id'=> Request::get('jour_type_id')])!!}";

$(function(){
    var dg = $('#dg').datagrid({
        url: get_url,
        pagination: true,
        // remoteFilter: true, //utk jalanin search filter
        rownumbers: true,
        singleSelect: true,
        fitColumns: true,
        pageList: [100,500,1000],
        pageSize:100,
    });
    // dg.datagrid('enableFilter');
});

$('#datepicker').datepicker({
      format: 'yyyy-mm-dd',
      autoclose: true
    });

 $(".choose-account").select2({
      ajax: {
        url: "{{route('ledger.select2')}}",
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
      minimumInputLength: 1
  });

 var coacode, coaname, depts;
 $("#addAccount").click(function(){
      coacode = $('#selectAccount option:selected').val();
      if(coacode != ""){
        $('#rowEmpty').hide();
        coaname = $('#selectAccount option:selected').data('name');
        depts = '<option value="">Choose Department</option> @foreach($departments as $dept)<option value="{{$dept->id}}">{{$dept->dept_name}}</option>@endforeach';
        $('#tableJournal').append('<tr><input type="hidden" name="coa_code[]" value="'+coacode+'"><td>'+coacode+'</td><td>'+coaname+'</td><td><input type="text" class="form-control" placeholder="description" name="ledg_description[]" required></td><td><select class="form-control" name="dept_code[]" required>'+depts+'</select></td><td><select name="type[]" class="form-control type"><option>debit</option><option>credit</option></select></td><td><input type="text" class="numeric form-control typeVal" name="typeVal[]" value=0 required></td><td><a href="#" class="removeLedger"><i class="fa fa-times text-danger"></i></a></td></tr>');
      }
 });

var total, totalDebit, totalCredit, val, type;
 function updateCounterDebit(){
    totalDebit = 0;
    $( ".type" ).each(function() {
        if($(this).val() == 'debit'){
          val = $(this).parent().parent().find('.typeVal').val();
          if(val=="") val = 0;
          totalDebit+=parseFloat(val);
        }
    });
    return totalDebit;
 }

 function updateCounterCredit(){
    totalCredit = 0;
    $( ".type" ).each(function() {
        if($(this).val() == 'credit'){
          val = $(this).parent().parent().find('.typeVal').val();
          if(val=="") val = 0;
          totalCredit+=parseFloat(val);
        }
    });
    return totalCredit;
 }

 function balanceStatus(){
    if($('#totalDebit').text() != $('#totalCredit').text()) $('#ledgerStatus').html('<span class="text-danger">unbalanced</span>');
    else $('#ledgerStatus').html('<span class="text-success">balanced</span>');
 }

function number_format(nStr)
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

 $(document).delegate('.typeVal','keyup',function(){
      type = $(this).parent().parent().find('.type').val();
      if(type == 'debit'){
        total = updateCounterDebit();
        $('#totalDebit').text("Rp. "+number_format(total));
      }else{
        total = updateCounterCredit();
        $('#totalCredit').text("Rp. "+number_format(total));
      }
      balanceStatus();
 }).delegate('.type','change',function(){
      total = updateCounterDebit();
        $('#totalDebit').text("Rp. "+number_format(total));
      total = updateCounterCredit();
        $('#totalCredit').text("Rp. "+number_format(total));
      balanceStatus();
 });

var formData;
 $('#formJournal').submit(function(e){
    e.preventDefault();
    var status = $('#ledgerStatus').text();
    if(status === 'unbalanced'){
        $.messager.alert('Warning', 'Make sure Journal entries is Balanced first');
    }else if(status === 'balanced'){
        formData = $(this).serialize();
        $.post('{{route('journal.insert')}}', formData, function(result){
            if(result.errorMsg) $.messager.alert('Warning',result.errorMsg);
            if(result.status){ 
              $.messager.alert('Warning',result.message);
              location.reload();
            }
        });
    }else{
        $.messager.alert('Warning', 'Make sure Journal entries is Balanced first');
    }
 });

 $(document).delegate('.numeric', 'keypress', function(e){
    var charCode = (e.which) ? e.which : event.keyCode;
    if ((charCode < 48 || charCode > 57))
        return false;

    return true;
 });

 $(document).delegate('.removeLedger','click',function(){
      if(confirm('Are you sure want to remove this ledger?')){
          $(this).parent().parent().remove();
          total = updateCounterDebit();
            $('#totalDebit').text("Rp. "+number_format(total));
          total = updateCounterCredit();
            $('#totalCredit').text("Rp. "+number_format(total));
          balanceStatus();
      }
 });

 $(document).delegate('.remove','click',function(){
      var r = confirm("Are you sure want to delete entry ?");
      if(r == true){
          var id = $(this).data('id');
          $.post('{{route('journal.delete')}}',{id:id},function(result){
              if(result.errorMsg) $.messager.alert('Warning',result.errorMsg);
              if(result.success){ 
                $.messager.alert('Success','Delete journal success');
                  location.reload();
              }
          });
      }
  });

 $(document).delegate('.getDetail','click',function(){
            $('#detailModalContent').html('<center><img src="{{ asset('img/loading.gif') }}"><p>Loading ...</p></center>');
            var id = $(this).data('id');
            $.post('{{route('journal.getdetail')}}',{id:id}, function(data){
                $('#detailModalContent').html(data);
            });
        });

      // edit
      $(document).delegate(".edit","click",function() {
          var id = $(this).data('id');
          $.post('{{route('journal.edittab')}}',{id:id},function(result){
              if(result.errorMsg){ $.messager.alert('Warning',result.errorMsg); }
              else{
                $('#tab_3').html(result);
              }
          });
          $('.nav-tabs a[href="#tab_3"]').tab('show');
      });

  $('#formFilter').submit(function(e){
    e.preventDefault();
    var coafilter = $('#selectAccount').val();
    var query = $(this).find('input[name=q]').val();
    var date = $(this).find('input[name=filterdate]').val();
    var dept = $(this).find('select[name=dept]').val();
    var jour_type_id = $(this).find('select[name=jour_type_id]').val();
    $('#dg').datagrid('load', {
        q: query,
        coa: coafilter,
        date: date,
        dept: dept,
        jour_type_id: jour_type_id
    });
    $('#dg').datagrid('reload');
 });

</script>
@endsection