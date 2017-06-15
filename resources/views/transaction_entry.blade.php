@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Transaction Entry
@endsection

<!-- page title -->
@section('contentheader_title')
   Transaction Entry
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
    </style>
@endsection

@section('contentheader_breadcrumbs')
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Transaction Entry</li>
    </ol>
@stop

@section('main-content')
<div class="row">
  <div class="col-md-12">
    <div class="nav-tabs-custom">
      <div class="tab-content">
        @if(Session::has('success'))
        <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
          <h4><i class="icon fa fa-check"></i> Insert entry success</h4>
        </div>
        @endif
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
            <div class="col-sm-6">
              <label>COA</label>
              <div class="input-group input-group-md">
                <select class="js-example-basic-single" id="selectAccount" style="width:100%">
                  <option value="">Choose Account</option>
                  @foreach($accounts as $key => $coa)
                      <option value="{{$coa->coa_code}}" data-name="{{$coa->coa_name}}">{{$coa->coa_code." ".$coa->coa_name}}</option>
                  @endforeach
                </select>
                <span class="input-group-btn">
                  <button type="button" id="addAccount" class="btn btn-default">Add Line</button>
                </span>
              </div>
            </div>
          </div>
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
              <button type="submit" class="btn btn-flat btn-primary" id="submitJournal">Submit</button>
            </div>
          </div>
        </form>
        <!-- add journal -->
      </div>
    </div>
  </div>
</div>
<!-- Modal extra -->
<div id="detailModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content" style="width:100%">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Journal Detail</h4>
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
<script type="text/javascript" src="{{ asset('plugins/select2/select2.full.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('plugins/datepicker/bootstrap-datepicker.js') }}"></script>
<script type="text/javascript">
$(function () {
  $(".js-example-basic-single").select2();
});

var entity = "Journal"; // nama si tabel, ditampilin di dialog
var get_url = "{!!route('journal.get', ['date'=> Request::get('filterdate'), 'dept'=> Request::get('dept'), 'jour_type_id'=> Request::get('jour_type_id')])!!}";


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

</script>
@endsection