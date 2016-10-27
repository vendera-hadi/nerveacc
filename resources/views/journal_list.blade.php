@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Journal Entries
@endsection

<!-- page title -->
@section('contentheader_title')
   Journal Entries
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
        <li class="active">Journal Entries</li>
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
              <li><a href="#tab_2" data-toggle="tab">Add Journal Entry</a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="tab_1">
                <div class="row">
                    <div class="col-sm-6">
                    <!-- date range -->
                <form action="" method="GET">
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
                    <button type="submit" class="btn btn-success">Filter</button>
                </div>

              </div> 
            </form>

                  <!-- template tabel -->
                <table id="dg" title="Latest Journal Entry" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
                    <!-- kolom -->
                    <thead>
                        <tr>
                            <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                            <th field="ledg_date" width="120" sortable="true">Date</th>
                            <th field="ledg_refno" width="120" sortable="true">Ref No</th>
                            <th field="ledg_description" width="120" sortable="true">Description</th>
                            <th field="ledg_debit" width="120" sortable="true">Debit</th>
                            <th field="ledg_credit" width="120" sortable="true">Credit</th>
                            
                            <th field="contr_status" width="120" sortable="true">Status</th>
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
                          <label>Description</label>
                          <textarea class="form-control" name="ledg_description" required></textarea>
                        </div>
                      </div>

                      <div class="col-sm-6">
                        <div class="form-group">
                          <label>Department</label>
                          <select class="form-control" name="dept_code" required>
                            <option value="">Choose Department</option>
                            @foreach($departments as $dept)
                            <option value="{{$dept->dept_code}}">{{$dept->dept_name}}</option>
                            @endforeach
                          </select>
                        </div>

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
                            <select class="form-control" id="selectAccount" style="width:100%">
                              <option value="">Choose Account</option>
                              <?php $tempGroup = ''; ?>
                              @foreach($accounts as $key => $coa)
                                @if($coa->coa_type != $tempGroup && $key > 0){!!'</optgroup>'!!}@endif
                                @if($coa->coa_type != $tempGroup){!!'<optgroup label="'.$coa->coa_type.'">'!!}@endif
                                  <option value="{{$coa->coa_code}}" data-name="{{$coa->coa_name}}">{{$coa->coa_code." ".$coa->coa_name}}</option>
                                
                                <?php $tempGroup = $coa->coa_type; ?>
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
                                <td>Debit</td>
                                <td>Credit</td>
                                <td></td>
                              </tr>
                              
                              <tr id="rowEmpty">
                                <td colspan="5"><center>Data Kosong. Pilih account dan Add Line terlebih dulu</center></td>
                              </tr>

                              <tr class="text-center">
                                <td></td>
                                <td id="ledgerStatus"></td>
                                <td id="totalDebit" style="font-weight:bold; color:red">0</td>
                                <td id="totalCredit" style="font-weight:bold; color:green">0</td>
                                <td></td>
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
              </div>

            </div>
            <!-- /.tab-content -->
          </div>
          <!-- Tabs -->
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

var entity = "Journal"; // nama si tabel, ditampilin di dialog
var get_url = "{{route('journal.get', ['date'=> Request::get('filterdate')])}}";

$(function(){
    var dg = $('#dg').datagrid({
        url: get_url,
        pagination: true,
        remoteFilter: true, //utk jalanin search filter
        rownumbers: true,
        singleSelect: true,
        fitColumns: true
    });
    dg.datagrid('enableFilter');
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

 var coacode, coaname;
 $("#addAccount").click(function(){
      coacode = $('#selectAccount option:selected').val();
      if(coacode != ""){
        $('#rowEmpty').hide();
        coaname = $('#selectAccount option:selected').data('name');
        $('#tableJournal').append('<tr><input type="hidden" name="coa_code[]" value="'+coacode+'"><td>'+coacode+'</td><td>'+coaname+'</td><td><input type="text" class="numeric form-control debit" name="debit[]" value=0 required></td><td><input type="text" class="numeric form-control credit" name="credit[]" value=0 required></td><td><a href="#" class="removeLedger"><i class="fa fa-times text-danger"></i></a></td></tr>');
      }
 });

var total, totalDebit, totalCredit;
 function updateCounterDebit(){
    totalDebit = 0;
    $( ".debit" ).each(function() {
        totalDebit+=parseFloat($(this).val());
    });
    return totalDebit;
 }

 function updateCounterCredit(){
    totalCredit = 0;
    $( ".credit" ).each(function() {
        totalCredit+=parseFloat($(this).val());
    });
    return totalCredit;
 }

 $(document).delegate('.debit','keyup',function(){
    total = updateCounterDebit();
    $('#totalDebit').text(total);
    totalCredit = parseFloat($('#totalCredit').text());
    
    if(total != totalCredit) $('#ledgerStatus').html('<strong class="text-danger">unbalanced</strong>');
    else $('#ledgerStatus').html('<strong class="text-success">balanced</strong>');
 });

 $(document).delegate('.credit','keyup',function(){
    total = updateCounterCredit();
    $('#totalCredit').text(total);
    totalDebit = parseFloat($('#totalDebit').text());

    if(total != totalDebit) $('#ledgerStatus').html('<strong class="text-danger">unbalanced</strong>');
    else $('#ledgerStatus').html('<strong class="text-success">balanced</strong>');
 });

var formData;
 $('#formJournal').submit(function(e){
    e.preventDefault();
    var status = $('#ledgerStatus').text();
    if(status === 'unbalanced'){
        $.messager.alert('Warning', 'Make sure Journal entries is Balanced first');
    }else{
        formData = $(this).serialize();
        $.post('{{route('journal.insert')}}', formData, function(result){
            if(result.errorMsg) $.messager.alert('Warning',result.errorMsg);
            if(result.success) location.reload();
        });
    }
 });

 $(document).delegate('.numeric', 'keypress', function(e){
    var charCode = (e.which) ? e.which : event.keyCode;
    if ((charCode < 48 || charCode > 57))
        return false;

    return true;
 });
</script>
@endsection