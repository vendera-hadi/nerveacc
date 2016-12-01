<center>
  <p>Contract No : <strong>{{$contractNo}}</strong></p>
  <p>Unit : <strong>{{$unitCode}}</strong></p>
</center>

<form method="POST" id="formEditMeter">
@if(count($contInvMeter) > 0)
<h3>Input Last Meter (Meter Cost Item)</h3>
<input type="hidden" name="contr_id" value="{{$contr_id}}">
<input type="hidden" name="cutoff" value="{{$cutoffFlag}}">
<input type="hidden" name="tenan_id" value="{{$tenan_id}}">
<div class="table-responsive">
  <table  width="100%" class="table table-bordered" style="font-size:12px !important;">
      <tr>
        <th class="text-center">Cost Item</th>
        <th class="text-center">Meter Start</th>
        <th class="text-center">Meter End</th>
        <th class="text-center">Meter Used</th>
        <th class="text-center">Meter Cost Rate</th>
        <th class="text-center">Abodemen</th>
        <th class="text-center">Biaya Admin</th>
      </tr>
      @foreach($contInvMeter as $cinv)
      <tr class="text-center">
        <input type="hidden" name="unit_id[]" value="{{(int)$cinv->unit_id}}">
        <input type="hidden" name="costd_id[]" value="{{(int)$cinv->costd_id}}">
        <input type="hidden" name="meter_start[]" class="mtrstart" value="{{isset($lastMeter[$cinv->costd_id]) ? (int)$lastMeter[$cinv->costd_id] : 0}}">
        <input type="hidden" name="meter_rate[]" value="{{(int)$cinv->costd_rate}}">
        <input type="hidden" name="meter_burden[]" value="{{(int)$cinv->costd_burden}}">
        <input type="hidden" name="meter_admin[]" value="{{(int)$cinv->costd_admin}}">
        <td>{{$cinv->costd_name}}</td>
        <td>{{isset($lastMeter[$cinv->costd_id]) ? (int)$lastMeter[$cinv->costd_id] : 0}}</td>
        <td>
          <input type="text" name="meter_end[]" class="numeric meter_e" value="{{isset($lastMeter[$cinv->costd_id]) ? (int)$lastMeter[$cinv->costd_id] : 0}}" required>
        </td>
        <td class="mtrused">meter used</td>
        <td>{{"Rp. ".number_format($cinv->costd_rate)}}</td>
        <td>{{"Rp. ".number_format($cinv->costd_burden)}}</td>
        <td>{{"Rp. ".number_format($cinv->costd_admin)}}</td>
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

$(document).delegate('.numeric', 'keypress', function(e){
    var charCode = (e.which) ? e.which : event.keyCode;
    if ((charCode < 48 || charCode > 57))
        return false;

    return true;
 });
</script>