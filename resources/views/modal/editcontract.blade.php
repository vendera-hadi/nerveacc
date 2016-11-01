<div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#modaltab_1" data-toggle="tab">Information</a></li>
              <li><a href="#modaltab_2" data-toggle="tab">Cost Detail</a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="modaltab_1">    
    <!-- form edit -->
    <form method="POST" id="formEditContract">
        
        <div class="form-group">
            <label>Contract Code</label>
            <input type="text" name="contr_code" required="required" class="form-control" value="{{$fetch->contr_code}}">
        </div>
        <div class="form-group">
            <label>Contract No</label>
            <input type="text" name="contr_no" required="required" class="form-control" value="{{$fetch->contr_no}}">
        </div>
        <div class="form-group">
            <label>Contract Start Date</label>
            <div class="input-group date">
              <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </div>
              <input type="text" id="startDate" name="contr_startdate" required="required" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd" value="{{$fetch->contr_startdate}}">
            </div>
        </div>
        <div class="form-group">
            <label>Contract End Date</label>
            <div class="input-group date">
              <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </div>
              <input type="text" id="endDate" name="contr_enddate" required="required" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd" value="{{$fetch->contr_enddate}}">
            </div>
        </div>
        <div class="form-group">
            <label>Berita Acara Serah Terima Date</label>
            <div class="input-group date">
              <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </div>
              <input type="text" name="contr_bast_date" required="required" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd" value="{{$fetch->contr_bast_date}}">
            </div>
        </div>
        <div class="form-group">
            <label>Berita Acara Serah Terima By</label>
            <input type="text" name="contr_bast_by" required="required" class="form-control" value="{{$fetch->contr_bast_date}}">
        </div>
        <div class="form-group">
            <label>Note (optional)</label>
            <textarea name="contr_note" class="form-control" value="{{$fetch->note}}"></textarea>
        </div>
        <div class="form-group">
            <label>Tenant</label>
            <select class="form-control choose-tenant" name="tenan_id" required="required" style="width:100%">
            <option value="{{$fetch->tenan_id}}">{{$fetch->tenan_code." (".$fetch->tenan_name.")"}}</option>
            </select>
        </div>
        <div class="form-group">
            <label>Marketing Agent (optional)</label>
            <select class="form-control choose-marketing" name="mark_id" style="width:100%">
            <option value="{{$fetch->mark_id}}">{{$fetch->mark_code." (".$fetch->mark_name.")"}}</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Contract Status</label>
            <select class="form-control choose-ctrstatus" name="const_id" required="required" style="width:100%">
            <option value="{{$fetch->const_id}}">{{$fetch->const_name}}</option>
            </select>
        </div>

        <div class="row">
            <div class="col-xs-6">
                <div class="form-group">
                    <label>Unit</label>
                        <div class="input-group">
                          <input type="hidden" name="unit_id" id="txtUnitEditId" required>
                          <input type="text" class="form-control" id="txtUnitEdit" disabled>
                          <span class="input-group-btn">
                            <button class="btn btn-info" type="button" id="chooseUnitButtonEdit">Choose Unit</button>
                          </span>
                        </div><!-- /input-group -->
                </div>
            </div>

            <div class="col-xs-6">
                <div class="form-group">
                    <label>Virtual Account</label>
                    <input type="hidden" name="viracc_id" id="txtVAEditId" required>
                    <input type="text" class="form-control" id="txtVAEdit" value="{{$fetch->viracc_id}}" disabled>
                    
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-default">Submit</button>
    </form>
    <!-- form edit -->
              </div>
              <!-- /.tab-pane -->
              <div class="tab-pane" id="modaltab_2">
       <div class="form-group">
                      <label>Choose Cost Items</label>
                      <select id="selectCostItemEdit" class="form-control" name="costdt[]">
                          @foreach($cost_items as $citm)
                          <option value="{{$citm->id}}">{{$citm->cost_name}} ({{$citm->cost_code}})</option>
                          @endforeach
                      </select>
                  </div>
      <button type="button" id="clickCostItemEdit">Add Cost Item</button>
      <button type="button" id="clickManualCostItemEdit">New Cost Item</button>

      <br><br>
      <form method="POST" id="formEditCostItem">
      <table id="editTableCost" width="100%" class="table table-bordered">
          <tr class="text-center">
            <td>Cost Item</td>
            <td>Edit Details</td>
            <td></td>
          </tr>
          @foreach($costdetail as $cdt)
          <tr class="text-center">
            <input type="hidden" name="contr_id[]" value="{{$fetch->id}}">
            <input type="hidden" name="costd_id[]" value="{{$cdt->id}}">
            <td>{{$cdt->cost_name}} ({{$cdt->cost_code}})</td>
            <td>
              <strong>Name :</strong> {{$cdt->costd_name}}<br>
              <strong>Unit :</strong> {{$cdt->costd_unit}}<br>
              <strong>Cost Rate :</strong> {{$cdt->costd_rate}}<br>
              <strong>Cost Burden :</strong> {{$cdt->costd_burden}}<br>
              <strong>Cost Admin :</strong> {{$cdt->costd_admin}}<br>
              <strong>Use Meter :</strong> @if($cdt->costd_ismeter){{'yes'}}@else{{'no'}}@endif<br>
              
              <strong>Invoice Type :</strong> <select name="inv_type[]" class="form-control">
                @foreach($invoice_types as $invtype)
                <option value="{{$invtype->invtp_code}}" @if($cdt->invtp_code == $invtype->invtp_code){{'selected="selected"'}}@endif>{{$invtype->invtp_name}}</option>
                @endforeach
              </select>
            </td>
            <td>
              <a href="#" class="removeCost">
                <i class="fa fa-times text-danger"></i>
              </a>
            </td>
          </tr>
          @endforeach
      </table>
      <center><button type="submit">Submit</button></center>
    </form>
              </div>
              
  </div>
  <!-- /.tab-content -->
</div>



    <script>
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

    $(".choose-rental").select2({
          ajax: {
            url: "{{route('rentalperiod.select2')}}",
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

    $('#formEditContract').submit(function(e){
        e.preventDefault();
        var data = $(this).serialize();
        $.post('{{route('contract.update',['id' => $fetch->id])}}',data, function(result){
            alert(result.message);
            if(result.status == 1) location.reload();
        });
    });

    var invoiceTypes = '{!!$inv_types_options!!}';
    var contractID = '{!!$fetch->id!!}';
    $('#clickCostItemEdit').click(function(){

            costItem = $('#selectCostItemEdit').val();
            costItemName = $('#selectCostItemEdit option:selected').text();
            $.post('{{route('cost_item.getDetail')}}', {id: costItem}, function(result){
                $.each( result, function( i, val ) {
                    $('#editTableCost').append('<tr class="text-center"><input type="hidden" name="contr_id[]" value="'+contractID+'"><input type="hidden" name="costd_id[]" value="'+val.id+'"><td>'+costItemName+'</td><td><strong>Name :</strong> '+val.costd_name+'<br><strong>Unit :</strong> '+val.costd_unit+'<br><strong>Cost Rate :</strong> '+val.costd_rate+'<br><strong>Cost Burden :</strong> '+val.costd_burden+'<br><strong>Cost Admin :</strong> '+val.costd_admin+'<br><strong>Use Meter :</strong> '+val.costd_ismeter+'<br><strong>Invoice Type :</strong> <select name="inv_type[]" class="form-control">'+invoiceTypes+'</select></td><td><a href="#" class="removeCost"><i class="fa fa-times text-danger"></i></a></td></tr>');              
                });
            });
            // $('#editTableCost').append('<tr class="text-center"><input type="hidden" name="contr_id[]" value="'+contractID+'"><input type="hidden" name="cost_id[]" value="'+costItem+'"><td>'+costItemName+'</td><td><strong>Name :</strong> <input type="text" name="costd_name[]" class="form-control costd_name"  required><strong>Unit :</strong> <input type="text" name="costd_unit[]" class="form-control costd_unit" required><strong>Cost Rate :</strong> <input type="text" name="costd_rate[]" class="form-control costd_rate" required><strong>Cost Burden :</strong> <input type="text" name="costd_burden[]" class="form-control costd_burden" required><strong>Cost Admin :</strong> <input type="text" name="costd_admin[]" class="form-control costd_admin" required><strong>Invoice Type :</strong> <select name="inv_type[]" class="form-control">'+invoiceTypes+'</select><strong>Use Meter :</strong> <select name="is_meter[]" class="form-control"><option value="1">yes</option><option value="0">no</option></select></td><td><a href="#" class="removeCost"><i class="fa fa-times text-danger"></i></a></td></tr>');
    });

    $('#clickManualCostItemEdit').click(function(){
        $('#editTableCost').append('<tr class="text-center"><input type="hidden" name="contr_id[]" value="'+contractID+'"><td><input type="text" name="cost_name[]" class="form-control" placeholder="Cost Item Name" required><br><input type="text" name="cost_code[]" class="form-control" placeholder="Cost Item Code" required></td><td><input type="text" name="costd_name[]" class="form-control costd_name" placeholder="Cost Detail Name" required><br><input type="text" name="costd_unit[]" class="form-control costd_unit"  placeholder="Unit" required><br><input type="text" name="costd_rate[]" placeholder="Rate" class="form-control costd_rate" required><br><input type="text" name="costd_burden[]" placeholder="Abonemen" class="form-control costd_burden" required><br><input type="text" name="costd_admin[]" placeholder="Biaya Admin" class="form-control costd_admin" required><br><select name="is_meter[]" class="form-control"><option value="1">yes</option><option value="0">no</option></select><br><select name="inv_type_custom[]" class="form-control">'+invoiceTypes+'</select></td><td><a href="#" class="removeCost"><i class="fa fa-times text-danger"></i></a></td></tr>');
    });


    $('#formEditCostItem').submit(function(e){
        e.preventDefault();
        if(!$(this).serialize()){
            alert('Please fill the Cost Item first');
        }else{
            var data = $(this).serialize();
            // console.log(data);
            $.post('{{route('contract.cdtupdate')}}',data, function(result){
                alert(result.message);
                if(result.status == 1) location.reload();
            });
        }
    });


        
    </script>