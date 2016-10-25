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
            <label>Contract Parent</label>
            <select class="form-control contract-parent" style="width:100%" name="contr_parent" required="required">
            <option value="@if($fetch->contr_parent!=0)){{$fetch->contr_parent}}@else{{'0'}}@endif">@if($fetch->contr_parent!=0){{$fetch->parent_code.' ('.$fetch->parent_no.')'}}@else{{'No Parent'}}@endif</option>
            </select>
        </div>
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
              <input type="text" name="contr_startdate" required="required" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd" value="{{$fetch->contr_startdate}}">
            </div>
        </div>
        <div class="form-group">
            <label>Contract End Date</label>
            <div class="input-group date">
              <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </div>
              <input type="text" name="contr_enddate" required="required" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd" value="{{$fetch->contr_enddate}}">
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
            <label>Note</label>
            <textarea name="contr_note" class="form-control" value="{{$fetch->note}}"></textarea>
        </div>
        <div class="form-group">
            <label>Tenant</label>
            <select class="form-control choose-tenant" name="tenan_id" required="required" style="width:100%">
            <option value="{{$fetch->tenan_id}}">{{$fetch->tenan_code." (".$fetch->tenan_name.")"}}</option>
            </select>
        </div>
        <div class="form-group">
            <label>Marketing Agent</label>
            <select class="form-control choose-marketing" name="mark_id" required="required" style="width:100%">
            <option value="{{$fetch->mark_id}}">{{$fetch->mark_code." (".$fetch->mark_name.")"}}</option>
            </select>
        </div>
        <div class="form-group">
            <label>Virtual Account</label>
            <select class="form-control choose-vaccount" name="viracc_id" required="required" style="width:100%">
            <option value="{{$fetch->viracc_id}}">{{$fetch->viracc_no." (".$fetch->viracc_name.")"}}</option>
            </select>
        </div>
        <div class="form-group">
            <label>Contract Status</label>
            <select class="form-control choose-ctrstatus" name="const_id" required="required" style="width:100%">
            <option value="{{$fetch->const_id}}">{{$fetch->const_name}}</option>
            </select>
        </div>

        <div class="form-group">
            <label>Unit</label>
            <select class="form-control choose-unit" name="unit_id" required="required" style="width:100%">
            <option value="{{$fetch->unit_id}}">{{$fetch->unit_name." (".$fetch->unit_code.")"}}</option>
            </select>
        </div>
        <button type="submit" class="btn btn-default">Submit</button>
    </form>
    <!-- form edit -->
              </div>
              <!-- /.tab-pane -->
              <div class="tab-pane" id="modaltab_2">
    <form method="POST" id="formEditCostItem">
      <input type="hidden" name="contr_id" value="{{$fetch->contr_id}}">
      <table id="tableCost" width="100%" class="table table-bordered">
          <tr class="text-center">
            <td>Cost Item</td>
            <td>Edit Details</td>
            <td></td>
          </tr>
          @foreach($costdetail as $cdt)
          <tr class="text-center">
            <input type="hidden" name="cost_id[]" value="{{$cdt->cost_id}}">
            <td>{{$cdt->cost_name}} ({{$cdt->cost_code}})</td>
            <td>
              <strong>Name :</strong> <input type="text" name="costd_name[]" class="form-control costd_name" value="{{$cdt->costd_name}}" required>
              <strong>Unit :</strong> <input type="text" name="costd_unit[]" class="form-control costd_unit" value="{{$cdt->costd_unit}}" required>
              <strong>Cost Rate :</strong> <input type="text" name="costd_rate[]" class="form-control costd_rate" value="{{$cdt->costd_rate}}" required>
              <strong>Cost Burden :</strong> <input type="text" name="costd_burden[]" class="form-control costd_burden" value="{{$cdt->costd_burden}}" required>
              <strong>Cost Admin :</strong> <input type="text" name="costd_admin[]" class="form-control costd_admin" value="{{$cdt->costd_admin}}" required>
              
              <strong>Invoice Type :</strong> <select name="inv_type[]" class="form-control">
                @foreach($invoice_types as $invtype)
                <option value="{{$invtype->invtp_code}}" @if($fetch->invtp_code == $invtype->invtp_code){{'selected="selected"'}}@endif>{{$invtype->invtp_name}}</option>
                @endforeach
              </select>

              <strong>Use Meter :</strong> <select name="is_meter[]" class="form-control">
                <option value="1" @if($cdt->costd_ismeter){{'selected="selected"'}}@endif>yes</option>
                <option value="0" @if(!$cdt->costd_ismeter){{'selected="selected"'}}@endif>no</option>
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
    </script>