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
    .datepicker{z-index:999 !important;}

    .pagination > li > span{
      padding-bottom: 9px;
    }
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
                                  <input type="text" id="startDate" name="contr_startdate" required="required" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Contract End Date</label>
                                <div class="input-group date">
                                  <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                  </div>
                                  <input type="text" id="endDate" name="contr_enddate" required="required" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd">
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
                                <label>Note (optional)</label>
                                <textarea name="contr_note" class="form-control"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Tenant</label>
                                <select class="form-control choose-tenant" name="tenan_id" required="required" style="width:100%">
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Marketing Agent (optional)</label>
                                <select class="form-control choose-marketing" name="mark_id" style="width:100%">
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label>Unit</label>
                                            <div class="input-group">
                                              <input type="hidden" name="unit_id" id="txtUnitId" required>
                                              <input type="text" class="form-control" id="txtUnit" disabled>
                                              <span class="input-group-btn">
                                                <button class="btn btn-info" type="button" id="chooseUnitButton">Choose Unit</button>
                                              </span>
                                            </div><!-- /input-group -->
                                    </div>
                                </div>
                                
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label>Virtual Account</label>
                                        <input type="hidden" name="viracc_id" id="txtVAId" required>
                                        <input type="text" class="form-control" id="txtVA" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Contract Status</label>
                                <select class="form-control choose-ctrstatus" name="const_id" required="required" style="width:100%">
                                </select>
                            </div>

                            
                            <button type="submit" class="btn btn-default">Next</button>
                        </form>
                        </div>

                        <div id="contractStep2" style="display:none">
                        <!-- Form step 2 -->
                                <h4>Cost Detail</h4>
                  <div class="form-group">
                      <label>Choose Cost Items</label>
                      <select id="selectCostItem" class="form-control" name="costdt[]">
                          <?php $tempGroup = ''; ?>
                          @foreach($cost_items as $key => $citm)
                            @if($citm->cost_name != $tempGroup && $key > 0){!!'</optgroup>'!!}@endif
                            @if($citm->cost_name != $tempGroup){!!'<optgroup label="'.$citm->cost_name.' ('.$citm->cost_code.')">'!!}@endif
                            <option value="{{$citm->id}}">{{$citm->costd_name}}</option>
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
                      <td width="85">Use Meter</td>
                      <td>Invoice Type</td>
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

        $('#formContract').submit(function(e){
            e.preventDefault();
            var startdate = $('#startDate').val();
            var enddate = $('#endDate').val();
            if(new Date(enddate) <= new Date(startdate)){
                $.messager.alert('Warning','Start Date must be lower than End Date');
            }else if($('#txtUnitId').val() == ""){
              $.messager.alert('Warning','Unit is required');
            }else if($('#txtVAId').val() == ""){
              $.messager.alert('Warning','Virtual Account is required');
            }else{
              $('#contractStep1').hide();
              $('#contractStep2').show();
            }
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
                    if(result.status == 1) location.reload();
                });
            }
        });

        $('#backStep1').click(function(){
            $('#contractStep1').show();
            $('#contractStep2').hide();
        });

        var costItem, unit;
        var invoiceTypes = '{!!$invoice_types!!}';
        var choices = [];
        $('#clickCostItem').click(function(){
            $('#tableCost').show();
            costDetail = $('#selectCostItem').val();
            costDetailName = $('#selectCostItem option:selected').text();
            $.post('{{route('cost_item.getDetail')}}', {id: costDetail}, function(result){
                $('#tableCost').append('<tr class="text-center"><input type="hidden" name="costd_is[]" value="'+result.id+'"><td>'+result.costitem.cost_name+'</td><td>'+result.costd_name+'</td><td>-</td><td>'+result.costd_rate+'</td><td>'+result.costd_burden+'</td><td>'+result.costd_admin+'</td><td>'+result.costd_ismeter+'</td><td><select name="inv_type[]" class="form-control">'+invoiceTypes+'</select></td><td><a href="#" class="removeCost"><i class="fa fa-times text-danger"></i></a></td></tr>');              
            });
        });

        // $('#clickManualCostItem').click(function(){
        //     $('#tableCost').show();
        //     unit = $('.choose-unit option:selected').text();
        //     unit = unit.substring(unit.indexOf('(') + 1, unit.indexOf(')'));
        //     $('#tableCost').append('<tr class="text-center"><td><input type="text" name="cost_name[]" placeholder="New Cost Item Name" required><br><br><input type="text" name="cost_code[]" placeholder="New Cost Item Code" required></td><td><input type="text" name="costd_name[]" class="form-control costd_name" placeholder="Cost Detail Name" required></td><td><input type="text" name="costd_unit[]" class="form-control costd_unit" value="'+unit+'" placeholder="Unit" required></td><td><input type="text" name="costd_rate[]" placeholder="Rate" class="form-control costd_rate" required></td><td><input type="text" name="costd_burden[]" placeholder="Abonemen" class="form-control costd_burden" required></td><td><input type="text" name="costd_admin[]" placeholder="Biaya Admin" class="form-control costd_admin" required></td><td><select name="is_meter[]" class="form-control"><option value="1">yes</option><option value="0">no</option></select></td><td><select name="inv_type_custom[]" class="form-control">'+invoiceTypes+'</select></td><td><a href="#" class="removeCost"><i class="fa fa-times text-danger"></i></a></td></tr>');
        // });

        // $(document).delegate(".costd_rate,.costd_burden,.costd_admin", "keypress", function(e) {
        //     var charCode = (e.which) ? e.which : event.keyCode;
        //     if ((charCode < 48 || charCode > 57))
        //         return false;

        //     return true;
        // });


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

        var currenturl; 
        $('#chooseUnitButton').click(function(){
            $('#unitModal').modal("show");
            currenturl = '{{route('unit.popup')}}';
            $.post(currenturl,null, function(data){
                $('#unitModalContent').html(data);
            });
        });

        $(document).delegate('.pagination li a','click',function(e){
            e.preventDefault();
            currenturl = $(this).attr('href');
            $.post(currenturl, null, function(data){
                $('#unitModalContent').html(data);
            });
        });

        $(document).delegate('#searchUnit','submit',function(e){
            e.preventDefault();
            var data = $('#searchUnit').serialize();
            currenturl = '{{route('unit.popup')}}';
            $.post(currenturl, data, function(data){
                $('#unitModalContent').html(data);
            });
        });

        $(document).delegate('#chooseUnit','click',function(e){
            e.preventDefault();
            var unitid = $('input[name="unit"]:checked').val();
            var unitname = $('input[name="unit"]:checked').data('name');
            $('#txtUnitId').val(unitid);
            $('#txtUnit').val(unitname);
            var unitvaccount = $('input[name="unit"]:checked').data('vaccount');
            $('#txtVAId,#txtVA').val(unitvaccount);
            $('#unitModalContent').text('');
            $('#unitModal').modal("hide");
        });

        $(document).delegate('#chooseUnitButtonEdit','click',function(){
            $('#editModal').modal('hide');
            $('#unitModal').modal("show");
            currenturl = '{{route('unit.popup')}}';
            $.post(currenturl, {edit:true}, function(data){
                $('#unitModalContent').html(data);
            });
        });

        $(document).delegate('#searchUnitEdit','submit',function(e){
            e.preventDefault();
            var data = $('#searchUnitEdit').serialize();
            currenturl = '{{route('unit.popup')}}?edit=true';
            $.post(currenturl, data, function(data){
                $('#unitModalContent').html(data);
            });
        });

        $(document).delegate('#chooseUnitEdit','click',function(e){
            e.preventDefault();
            var unitid = $('input[name="unitedit"]:checked').val();
            var unitname = $('input[name="unitedit"]:checked').data('name');
            $('#txtUnitEditId').val(unitid);
            $('#txtUnitEdit').val(unitname);
            var unitvaccount = $('input[name="unitedit"]:checked').data('vaccount');
            $('#txtVAEditId,#txtVAEdit').val(unitvaccount);
            $('#editModal').modal('show');
            $('#unitModalContent').text('');
            $('#unitModal').modal("hide");
        });
</script>
@endsection
