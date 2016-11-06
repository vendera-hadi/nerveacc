@if ($st[0]->status === false)
<form method="POST" id="formEditMeter">
<div class="table-responsive">
  <table  width="100%" class="table table-bordered" style="font-size:12px !important;">
      <tr class="text-center">
        <th>No Contact</th>
        <th>No Unit</th>
        <th>Cost</th>
        <th>Meter Start</th>
        <th>Meter End</th>
        <th>Meter Used</th>
        <th>Meter Cost</th>
        <th>Abodemen</th>
        <th>Biaya Admin</th>
      </tr>
      @foreach($meter as $cdt)
      <tr class="text-center">
        <input type="hidden" name="tr_meter_id[]" value="{{$cdt->id}}">
        <input type="hidden" name="meter_start[]" value="{{$cdt->meter_start}}">
        <input type="hidden" name="meter_rate[]" value="{{$cdt->costd_rate}}">
        <input type="hidden" name="meter_burden[]" value="{{$cdt->meter_burden}}">
        <input type="hidden" name="meter_admin[]" value="{{$cdt->meter_admin}}">
        <td>{{$cdt->contr_no}}</td>
        <td>{{$cdt->contr_code}}</td>
        <td>{{$cdt->costd_name}}</td>
        <td>{{$cdt->meter_start}}</td>
        <td>
          <input type="text" name="meter_end[]" class="numeric meter_e" value="{{$cdt->meter_end}}">
        </td>
        <td>{{$cdt->meter_used}}</td>
        <td>{{number_format($cdt->meter_cost)}}</td>
        <td>{{number_format($cdt->meter_burden)}}</td>
        <td>{{number_format($cdt->meter_admin)}}</td>
      </tr>
      @endforeach
  </table>
</div>
<div class="text-left">
  <button type="button" id="upload" class="btn btn-xs btn-primary">Upload Excel</button>
  <button type="submit" class="btn btn-xs btn-info">Submit</button>
</div>
</form>

<script type="text/javascript">
  
  $('#formEditMeter').submit(function(e){
    e.preventDefault();
    if(!$(this).serialize()){
        alert('Please fill the meter');
    }else{
        var data = $(this).serialize();
        $.post('{{route('period_meter.cdtupdate')}}',data, function(result){
            alert(result.message);
            if(result.status == 1) location.reload();
        });
        
    }
});

</script>

@else
<div class="table-responsive">
  <table  width="100%" class="table table-bordered" style="font-size:12px !important;">
      <tr class="text-center">
        <th>No Contact</th>
        <th>No Unit</th>
        <th>Cost</th>
        <th>Meter Start</th>
        <th>Meter End</th>
        <th>Meter Used</th>
        <th>Meter Cost</th>
        <th>Abodemen</th>
        <th>Biaya Admin</th>
      </tr>
      @foreach($meter as $cdt)
      <tr class="text-center">
        <td>{{$cdt->contr_no}}</td>
        <td>{{$cdt->contr_code}}</td>
        <td>{{$cdt->costd_name}}</td>
        <td>{{$cdt->meter_start}}</td>
        <td>{{$cdt->meter_end}}</td>
        <td>{{$cdt->meter_used}}</td>
        <td>{{number_format($cdt->meter_cost)}}</td>
        <td>{{number_format($cdt->meter_burden)}}</td>
        <td>{{number_format($cdt->meter_admin)}}</td>
      </tr>
      @endforeach
  </table>
</div>
@endif