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