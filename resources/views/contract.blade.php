@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Contract
@endsection

<!-- page title -->
@section('contentheader_title')
   Contract
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
    .datepicker{z-index:1151 !important;}
    </style>
@endsection

@section('contentheader_breadcrumbs')
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Contract</li>
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
              <li><a href="#tab_2" data-toggle="tab">Add Contract</a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="tab_1">
                  <!-- template tabel -->
                <table id="dg" title="Contract" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
                    <!-- kolom -->
                    <thead>
                        <tr>
                            <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                            <th field="tenan_name" width="120" sortable="true">Tenant</th>
                            <th field="contr_code" width="120" sortable="true">Contract Code</th>
                            <th field="contr_no" width="120" sortable="true">Contract No</th>
                            <th field="contr_startdate" width="120" sortable="true">Start Date</th>
                            <th field="contr_enddate" width="120" sortable="true">End Date</th>
                            
                            <th field="contr_status" width="120" sortable="true">Status</th>
                            <th field="contr_terminate_date" width="120" sortable="true">Terminated Date</th>
                            <th field="action">Action</th>
                        </tr>
                    </thead>
                </table>
                <!-- end table -->
                
                
              </div>
              <!-- /.tab-pane -->
              <div class="tab-pane" id="tab_2">
                        <div id="contractStep1">
                        <form method="POST" id="formContract">
                            <div class="form-group">
                                <label>Contract Parent</label>
                                <select class="form-control contract-parent" style="width:100%" name="contr_parent" required="required">
                                <option value="0">No Parent</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Contract Code</label>
                                <input type="text" name="contr_code" required="required" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Contract No</label>
                                <input type="text" name="contr_no" required="required" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Contract Start Date</label>
                                <div class="input-group date">
                                  <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                  </div>
                                  <input type="text" name="contr_startdate" required="required" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Contract End Date</label>
                                <div class="input-group date">
                                  <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                  </div>
                                  <input type="text" name="contr_enddate" required="required" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Berita Acara Serah Terima Date</label>
                                <div class="input-group date">
                                  <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                  </div>
                                  <input type="text" name="contr_bast_date" required="required" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Berita Acara Serah Terima By</label>
                                <input type="text" name="contr_bast_by" required="required" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Note</label>
                                <textarea name="contr_note" class="form-control"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Tenant</label>
                                <select class="form-control choose-tenant" name="tenan_id" required="required" style="width:100%">
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Marketing Agent</label>
                                <select class="form-control choose-marketing" name="mark_id" required="required" style="width:100%">
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Virtual Account</label>
                                <select class="form-control choose-vaccount" name="viracc_id" required="required" style="width:100%">
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Contract Status</label>
                                <select class="form-control choose-ctrstatus" name="const_id" required="required" style="width:100%">
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Unit</label>
                                <select class="form-control choose-unit" name="unit_id" required="required" style="width:100%">
                                </select>
                            </div>
                            <button type="submit" class="btn btn-default">Submit</button>
                        </form>
                        </div>

                        <div id="contractStep2" style="display:none">
                        <!-- Form step 2 -->
                                <h4>Cost Detail</h4>
                  <div class="form-group">
                      <label>Choose Cost Items</label>
                      <select id="selectCostItem" class="form-control" name="costdt[]">
                          @foreach($cost_items as $citm)
                          <option value="{{$citm->id}}">{{$citm->cost_name}} ({{$citm->cost_code}})</option>
                          @endforeach
                      </select>
                  </div>
                  <button id="clickCostItem">Add Cost Item</button>
                  <br><br>
                  <form method="POST" id="formContract2">
                  <table id="tableCost" width="100%" class="table table-bordered" style="display: none">
                    <tr class="text-center">
                      <td>Cost Item</td>
                      <td>Name</td>
                      <td>Unit</td>
                      <td>Cost Rate</td>
                      <td>Cost Burden</td>
                      <td>Cost Admin</td>
                      <td>Invoice Type</td>
                      <td width="85">Use Meter</td>
                      <td></td>
                    </tr>
                    
                  </table>
                  <br><br>
                  <button type="button" id="backStep1">Back</button>
                  <button type="submit" >Submit</button>
                  </form>
                  <!-- form step 2 -->
                        </div>
              </div>

            </div>
            <!-- /.tab-content -->
          </div>
          <!-- Tabs -->
       


                <!-- content -->
            
                <!-- Modal extra -->
                <div id="detailModal" class="modal fade" role="dialog">
                  <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Contract Information</h4>
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

                

                <!-- modal form -->
                <div id="editModal" class="modal fade" role="dialog">
                  <div class="modal-dialog" style="width:80%">

                    <!-- Modal content-->
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Edit Contract</h4>
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
        $(".contract-parent").select2({
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
              minimumInputLength: 1
        });

        $(".choose-tenant").select2({
              ajax: {
                url: "{{route('tenant.select2')}}",
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

        $(".choose-marketing").select2({
              ajax: {
                url: "{{route('marketing.select2')}}",
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

        

        $(".choose-vaccount").select2({
              ajax: {
                url: "{{route('vaccount.select2')}}",
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

        $(".choose-ctrstatus").select2({
              ajax: {
                url: "{{route('contractstatus.select2')}}",
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

        $(".choose-unit").select2({
              ajax: {
                url: "{{route('unit.select2')}}",
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

        $('#formContract').submit(function(e){
            e.preventDefault();
            $('#contractStep1').hide();
            $('#contractStep2').show();
            console.log('submit');
        });

        $('#formContract2').submit(function(e){
            e.preventDefault();
            if(!$('#formContract2').serialize()){
                alert('Please fill the Cost Item first');
            }else{
                var allFormData = $('#formContract,#formContract2').serialize();
                console.log(allFormData);
                $.post('{{route('contract.insert')}}',allFormData, function(result){
                    alert(result.message);
                    // if(result.status == 1) location.reload();
                });
            }
        });

        $('#backStep1').click(function(){
            $('#contractStep1').show();
            $('#contractStep2').hide();
        });

        var costItem, unit;
        var invoiceTypes = '{!!$invoice_types!!}';
        $('#clickCostItem').click(function(){
            $('#tableCost').show();
            unit = $('.choose-unit option:selected').text();
            unit = unit.substring(unit.indexOf('(') + 1, unit.indexOf(')'));
            costItem = $('#selectCostItem').val();
            costItemName = $('#selectCostItem option:selected').text();
            $('#tableCost').append('<tr class="text-center"><input type="hidden" name="cost_id[]" value="'+costItem+'"><td>'+costItemName+'</td><td><input type="text" name="costd_name[]" class="form-control costd_name" required></td><td><input type="text" name="costd_unit[]" class="form-control costd_unit" value="'+unit+'" required></td><td><input type="text" name="costd_rate[]" class="form-control costd_rate" required></td><td><input type="text" name="costd_burden[]" class="form-control costd_burden" required></td><td><input type="text" name="costd_admin[]" class="form-control costd_admin" required></td><td><select name="inv_type[]" class="form-control">'+invoiceTypes+'</select></td><td><select name="is_meter[]" class="form-control"><option value="1">yes</option><option value="0">no</option></select></td><td><a href="#" class="removeCost"><i class="fa fa-times text-danger"></i></a></td></tr>');
        });

        $(document).delegate(".costd_rate,.costd_burden,.costd_admin", "keypress", function(e) {
            var charCode = (e.which) ? e.which : event.keyCode;
            if ((charCode < 48 || charCode > 57))
                return false;

            return true;
        });


        $(document).delegate('.remove','click',function(){
            var r = confirm("Are you sure want to delete entry ?");
            if(r == true){
                var id = $(this).data('id');
                $.post('{{route('contract.delete')}}',{id:id},function(result){
                    if(result.errorMsg) $.messager.alert('Warning',result.errorMsg);
                    if(result.success) location.reload();
                });
                // location.reload();
            }
        });

        $(document).delegate('.removeCost','click',function(){
            if(confirm('Are you sure want to remove this cost item?')){
                $(this).parent().parent().remove();
            }
        });

        $(document).delegate('.edit','click',function(){
            var id = $(this).data('id');
            $.post('{{route('contract.detail')}}',{id:id},function(result){
                if(result.errorMsg) $.messager.alert('Warning',result.errorMsg);
                else $('#editModal').find('.modal-body').html(result);
            });
        });

        $('.datepicker').datepicker({
            autoclose: true
        });

        var entity = "Contract"; // nama si tabel, ditampilin di dialog
        var get_url = "{{route('contract.get')}}";

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

        $(document).delegate('.getDetail','click',function(){
            $('#detailModalContent').html('<center><img src="{{ asset('img/loading.gif') }}"><p>Loading ...</p></center>');
            var id = $(this).data('id');
            $.post('{{route('contract.getdetail')}}',{id:id}, function(data){
                $('#detailModalContent').html(data);
            });
        });
</script>
@endsection
