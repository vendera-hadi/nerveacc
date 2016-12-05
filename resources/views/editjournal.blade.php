<form method="POST" id="formEditJournal">
  <input type="hidden" name="id" value="{{$id}}">
    <div class="row">

      <div class="col-sm-6">
        <div class="form-group">
              <label>Journal Number</label>
              <input type="hidden" name="ledg_number" value="{{$fetch[0]->ledg_number}}">
              <input type="text" class="form-control" value="{{$fetch[0]->ledg_number}}" disabled>
        </div>
      </div>

      <div class="col-sm-6">
          <div class="form-group">
              <label>Date</label>
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input type="text" class="form-control pull-right" name="ledg_date" value="{{$fetch[0]->ledg_date}}" id="datepickerEdit" required>
              </div>
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
              <option value="{{$jourtype->id}}" @if($fetch[0]->jour_type_id == $jourtype->id){{'selected="selected"'}}@endif>{{$jourtype->jour_type_name}}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="col-sm-6">
        <div class="form-group">
          <label>Ref No</label>
          <input type="text" class="form-control" name="ledg_refno" value="{{$fetch[0]->ledg_refno}}" required>
        </div>
      </div>

    </div>



    <div class="AccountList" style="margin-top:30px">
        <div class="row">
          <div class="col-sm-6">
              <select class="form-control js-example-basic-single" id="selectAccountEdit" style="width:100%">
                <option value="">Choose Account</option>
                <?php $tempGroup = ''; ?>
                @foreach($accounts as $key => $coa)
                    <option value="{{$coa->coa_code}}" data-name="{{$coa->coa_name}}">{{$coa->coa_code." ".$coa->coa_name}}</option>
                @endforeach
              </select>
          </div>
          <div class="col-sm-2">
              <button type="button" id="addAccountEdit" class="btn btn-default">Add Line</button>
          </div>
        </div>
    </div>
    <br><br>
    <div class="row">
        <div class="col-sm-12">
            <table id="tableJournalEdit" width="100%" class="table table-bordered">
                <tr class="text-center">
                  <td>Account Code</td>
                  <td>Account Name</td>
                  <td>Description</td>
                  <td>Department</td>
                  <td>Debit/Credit</td>
                  <td>Value</td>
                  <td></td>
                </tr>
                
                <tr id="rowEmptyEdit" style="display:none">
                  <td colspan="7"><center>Data Kosong. Pilih account dan Add Line terlebih dulu</center></td>
                </tr>
                <?php $totalDebit = 0; $totalCredit = 0; ?>
                @foreach($fetch as $ledger)
                <tr>
                  <input type="hidden" name="coa_code[]" value="{{$ledger->coa_code}}">
                  <td>{{$ledger->coa_code}}</td>
                  <td>{{$ledger->coa_name}}</td>
                  <td><input type="text" class="form-control" placeholder="description" name="ledg_description[]" value="{{$ledger->ledg_description}}" required></td>
                  <td>
                    <select class="form-control" name="dept_code[]" required> 
                      @foreach($departments as $dept)
                      <option value="{{$dept->id}}" @if($ledger->dept_id == $dept->id){!!'selected="selected"'!!}@endif>{{$dept->dept_name}}</option>
                      @endforeach
                    </select>
                  </td>
                  <td>
                    <select name="type[]" class="form-control typeEdit">
                        <option @if($ledger->ledg_debit > 0){{'selected="selected"'}}@endif>debit</option>
                        <option @if($ledger->ledg_credit > 0){{'selected="selected"'}}@endif>credit</option>
                    </select>
                  </td>
                  <td>
                    <input type="text" class="numeric form-control typeValEdit" name="typeVal[]" value="@if($ledger->ledg_debit > 0){{number_format($ledger->ledg_debit,0,',','')}}@else{{number_format($ledger->ledg_credit,0,',','')}}@endif" required>
                  </td>
                  <td>
                    <a href="#" class="removeLedgerEdit"><i class="fa fa-times text-danger"></i></a>
                  </td>
                </tr>
                <?php if($ledger->ledg_debit > 0) $totalDebit+=$ledger->ledg_debit; else $totalCredit+=$ledger->ledg_credit; ?>
                @endforeach
              </table>

              <table width="50%" class="table table-bordered">
                <tr class="text-center">
                  <td>Status</td>
                  <td>Total Debit</td>
                  <td>Total Credit</td>
                </tr>
                <tr class="text-center">
                  <td id="ledgerStatusEdit" style="font-weight:bold;"><span class="text-success">balanced</span></td>
                  <td id="totalDebitEdit" style="font-weight:bold; color:red">{{"Rp. ".number_format($totalDebit,0)}}</td>
                  <td id="totalCreditEdit" style="font-weight:bold; color:blue">{{"Rp. ".number_format($totalCredit,0)}}</td>
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

  <script type="text/javascript">
$(document).ready(function() {
  $(".js-example-basic-single").select2();
});
  
  $('#datepickerEdit').datepicker({
      format: 'yyyy-mm-dd',
      autoclose: true
    });

var coacode, coaname, depts;
 $("#addAccountEdit").click(function(){
      coacode = $('#selectAccountEdit option:selected').val();
      if(coacode != ""){
        $('#rowEmptyEdit').hide();
        coaname = $('#selectAccountEdit option:selected').data('name');
        depts = '<option value="">Choose Department</option> @foreach($departments as $dept)<option value="{{$dept->id}}">{{$dept->dept_name}}</option>@endforeach';
        $('#tableJournalEdit').append('<tr><input type="hidden" name="coa_code[]" value="'+coacode+'"><td>'+coacode+'</td><td>'+coaname+'</td><td><input type="text" class="form-control" placeholder="description" name="ledg_description[]" required></td><td><select class="form-control" name="dept_code[]" required>'+depts+'</select></td><td><select name="type[]" class="form-control typeEdit"><option>debit</option><option>credit</option></select></td><td><input type="text" class="numeric form-control typeValEdit" name="typeVal[]" value=0 required></td><td><a href="#" class="removeLedgerEdit"><i class="fa fa-times text-danger"></i></a></td></tr>');
      }
 });

  var total, totalDebit, totalCredit, val, type;
 function updateCounterDebit(){
    totalDebit = 0;
    $( ".typeEdit" ).each(function() {
        if($(this).val() == 'debit'){
          val = $(this).parent().parent().find('.typeValEdit').val();
          if(val=="") val = 0;
          totalDebit+=parseFloat(val);
        }
    });
    return totalDebit;
 }

 function updateCounterCredit(){
    totalCredit = 0;
    $( ".typeEdit" ).each(function() {
        if($(this).val() == 'credit'){
          val = $(this).parent().parent().find('.typeValEdit').val();
          if(val=="") val = 0;
          totalCredit+=parseFloat(val);
        }
    });
    return totalCredit;
 }

 function balanceStatus(){
    if($('#totalDebitEdit').text() != $('#totalCreditEdit').text()) $('#ledgerStatusEdit').html('<span class="text-danger">unbalanced</span>');
    else $('#ledgerStatusEdit').html('<span class="text-success">balanced</span>');
 }

 $(document).delegate('.typeValEdit','keyup',function(){
      type = $(this).parent().parent().find('.typeEdit').val();
      if(type == 'debit'){
        total = updateCounterDebit();
        $('#totalDebitEdit').text("Rp. "+number_format(total));
      }else{
        total = updateCounterCredit();
        $('#totalCreditEdit').text("Rp. "+number_format(total));
      }
      balanceStatus();
 }).delegate('.typeEdit','change',function(){
      total = updateCounterDebit();
        $('#totalDebitEdit').text("Rp. "+number_format(total));
      total = updateCounterCredit();
        $('#totalCreditEdit').text("Rp. "+number_format(total));
      balanceStatus();
 });

 var formData;
 $('#formEditJournal').submit(function(e){
    e.preventDefault();
    var status = $('#ledgerStatusEdit').text();
    if(status === 'unbalanced'){
        $.messager.alert('Warning', 'Make sure Journal entries is Balanced first');
    }else if(status === 'balanced'){
        formData = $(this).serialize();
        $.post('{{route('journal.update')}}', formData, function(result){
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

 $('.removeLedgerEdit').click(function(){
      if(confirm('Are you sure want to remove this ledger?')){
          $(this).parent().parent().remove();
          total = updateCounterDebit();
            $('#totalDebitEdit').text("Rp. "+number_format(total));
          total = updateCounterCredit();
            $('#totalCreditEdit').text("Rp. "+number_format(total));
          balanceStatus();
      }
 });
  </script>