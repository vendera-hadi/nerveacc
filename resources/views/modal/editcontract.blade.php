<form method="POST" id="formContract">
    <div class="form-group">
        <label>Contract Parent</label>
        <select class="form-control contract-parent" style="width:100%" name="contr_parent" required="required">
        <option value="{{$fetch->contr_parent}}">{{$fetch->parent_code.' ('.$fetch->parent_no.')'}}</option>
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
        <label>Rental Period</label>
        <select class="form-control choose-rental" name="renprd_id" required="required" style="width:100%">
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
</script>