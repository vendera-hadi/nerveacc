<center>
  <p>Billing Info No : <strong>{{$contract->contr_no}}</strong></p>
  <p>Unit : <strong>{{$contract->MsUnit->unit_code}} ({{$contract->MsUnit->unit_sqrt}} m3)</strong></p>
  <p>Status : <strong>{{$cutoffFlag ? "Penyewa" : "Owner"}}</strong></p>
</center>

<form method="POST" id="formEditMeter">
@if(count($contInvMeter) > 0)
<h3>Meter Cost Item</h3>
<p>Silahkan memasukkan angka meter terakhir</p>
<input type="hidden" name="contr_id" value="{{$contr_id}}">
<input type="hidden" name="cutoff" value="{{$cutoffFlag}}">
<input type="hidden" name="tenan_id" value="{{$tenan_id}}">
<div class="table-responsive">
  <table  width="100%" class="table table-bordered" style="font-size:12px !important;">
      <tr>
        <th class="text-center">Cost Item</th>
        <th class="text-center">Last Meter</th>
        <th class="text-center">Meter End</th>
        <th class="text-center">Meter Used</th>
        <th class="text-center">Meter Cost Rate</th>
        <th class="text-center">Abodemen</th>
        <th class="text-center">Biaya Admin</th>
        <th class="text-center">BPJU</th>
        <th class="text-center">Gross up PPH</th>
        <th class="text-center">Public Area Variable</th>
      </tr>
      @foreach($contInvMeter as $cinv)
      <tr class="text-center">
        <input type="hidden" name="unit_id[]" value="{{(int)$cinv->unit_id}}">
        <input type="hidden" name="costd_id[]" value="{{(int)$cinv->costd_id}}">
        <input type="hidden" name="meter_start[]" class="mtrstart" value="{{isset($lastMeter[$cinv->costd_id]) ? (int)$lastMeter[$cinv->costd_id] : 0}}">
        <input type="hidden" name="meter_rate[]" value="{{(int)$cinv->costd_rate}}">
        <input type="hidden" name="meter_burden[]" value="{{(int)$cinv->costd_burden}}">
        <input type="hidden" name="meter_admin[]" value="{{(int)$cinv->costd_admin}}">
        <input type="hidden" name="cost_id[]" value="{{$cinv->cost_id}}">
        <input type="hidden" name="daya[]" value="{{$cinv->daya}}">
        <input type="hidden" name="percentage[]" value="{{empty($cinv->percentage) ? 0 : $cinv->percentage }}">
        <input type="hidden" name="value_type[]" value="{{$cinv->value_type}}">
        <input type="hidden" name="grossup[]" value="{{$cinv->grossup_pph}}">
        <td>{{$cinv->costd_name}}</td>
        <td>{{isset($lastMeter[$cinv->costd_id]) ? (int)$lastMeter[$cinv->costd_id] : 0}}</td>
        <td>
          <input type="text" name="meter_end[]" style="width: 60px" class="form-control numeric meter_e" value="{{isset($lastMeter[$cinv->costd_id]) ? (int)$lastMeter[$cinv->costd_id] : 0}}" required>
        </td>
        <td class="mtrused">-</td>
        <td>{{"Rp. ".number_format($cinv->costd_rate)}} @if($cinv->cost_id == 1){{'/KwH'}}@elseif($cinv->cost_id == 2){{'/m3'}}@endif</td>
        <td>{{"Rp. ".number_format($cinv->costd_burden)}}</td>
        <td>{{"Rp. ".number_format($cinv->costd_admin)}}</td>
        <td>@if($cinv->cost_id == 1){{$bpju." %"}}@else{{'-'}}@endif</td>
        <td>{{$cinv->grossup_pph ? 'yes' : 'no'}}</td>
        <td>@if($cinv->cost_id == 1){{empty($cinv->percentage) ? 'value' : 'percentage' }}@else{{'-'}}@endif</td>
      </tr>
      @endforeach
  </table>
</div>
@endif

@if(count($contInvNoMeter) > 0)
<h3>Non Meter Cost Item</h3>
<form method="POST" id="formEditNonMeter">
<div class="table-responsive">
  <table  width="100%" class="table table-bordered" style="font-size:12px !important;">
      <tr>
        <th class="text-center">Cost Item</th>
        <th class="text-center">Cost Rate</th>
        <th class="text-center">Abodemen</th>
        <th class="text-center">Biaya Admin</th>
      </tr>
      @foreach($contInvNoMeter as $cinv)
      <tr class="text-center">
        <input type="hidden" name="nonmeter_unit_id[]" value="{{(int)$cinv->unit_id}}">
        <input type="hidden" name="nonmeter_costd_id[]" value="{{(int)$cinv->costd_id}}">
        <input type="hidden" name="nonmeter_rate[]" value="{{$cinv->costd_rate}}">
        <input type="hidden" name="nonmeter_burden[]" value="{{$cinv->costd_burden}}">
        <input type="hidden" name="nonmeter_admin[]" value="{{$cinv->costd_admin}}">
        <td>{{$cinv->costd_name}}</td>
        <td>{{"Rp. ".number_format($cinv->costd_rate)}}</td>
        <td>{{"Rp. ".number_format($cinv->costd_burden)}}</td>
        <td>{{"Rp. ".number_format($cinv->costd_admin)}}</td>
      </tr>
      @endforeach
  </table>
</div>
@endif

<center>
  <b>Generate cost sampai tanggal ?</b>
  <input type="text" name="date" required="required" class="form-control datepicker" style="width: 120px" placeholder="input date" data-date-format="yyyy-mm-dd" value="{{!empty($contract->contr_terminate_date) ? $contract->contr_terminate_date : $contract->contr_enddate}}" required=""><br>
<button class="btn btn-info" id="generateInv">Generate Invoices</button>
</center>
</form>

<script type="text/javascript">
$('#formEditMeter').submit(function(e){
    e.preventDefault();
    var data = $(this).serialize();
    var flagContinue = true;
    $('.meter_e').each(function(){
        if(!$(this).val() || $(this).val()==0) flagContinue = false;
    });
    if(flagContinue){
        $.post('{{route('contract.closectr')}}', data, function(result){
            console.log(result);
            if(result.error){
                $.messager.alert('Warning',result.message);
            }
            if(result.success){
                $.messager.alert('Success',result.message);
                $('#closeCtrModal').modal("hide");
                $('#dg').datagrid('reload');
            }
        }, 'json');
    }
});

$('.meter_e').change(function(){
    var meterstart = parseInt($(this).parent().parent().find('.mtrstart').val());
    var meterused = $(this).parent().parent().find('.mtrused');
    if($(this).val() <= meterstart){
        alert('Meter end must be more than meter start');
        $(this).val(meterstart);
    }
    var totalUsed = parseInt($(this).val()) - meterstart;
    meterused.text(totalUsed);
});

$('.datepicker').datepicker({
            autoclose: true,
            startDate : new Date("{{!empty($contract->contr_terminate_date) ? date('F d, Y H:i:s',strtotime($contract->contr_terminate_date)) : date('F d, Y H:i:s',strtotime($contract->contr_enddate))}}")
        });

$(document).delegate('.numeric', 'keypress', function(e){
    var charCode = (e.which) ? e.which : event.keyCode;
    if ((charCode < 48 || charCode > 57))
        return false;

    return true;
 });
</script>