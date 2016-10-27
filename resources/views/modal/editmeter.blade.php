<form method="POST" id="formEditMeter">
  <table  width="100%" class="table table-bordered">
      <tr class="text-center">
        <td>No Contact</td>
        <td>No Unit</td>
        <td>Meter Start</td>
        <td>Meter End</td>
        <td>Meter Used</td>
        <td>Meter Cost</td>
        <td>Abodemen</td>
        <td>Biaya Admin</td>
      </tr>
      @foreach($meter as $cdt)
      <tr class="text-center">
        <input type="hidden" name="tr_meter_id[]" value="{{$cdt->id}}">
        <input type="hidden" name="meter_start[]" value="{{$cdt->meter_start}}">
        <input type="hidden" name="meter_rate[]" value="{{$cdt->costd_rate}}">
        <input type="hidden" name="meter_burden[]" value="{{$cdt->meter_burden}}">
        <input type="hidden" name="meter_admin[]" value="{{$cdt->meter_admin}}">
        <td>{{$cdt->contr_id}}</td>
        <td>{{$cdt->contr_code}}</td>
        <td>{{$cdt->meter_start}}</td>
        <td>
          <input type="text" name="meter_end[]" class="form-control meter_e" value="{{$cdt->meter_end}}">
        </td>
        <td>{{$cdt->meter_used}}</td>
        <td>{{number_format($cdt->meter_cost)}}</td>
        <td>{{number_format($cdt->meter_burden)}}</td>
        <td>{{number_format($cdt->meter_admin)}}</td>
      </tr>
      @endforeach
  </table>
  <center><button type="submit">Submit</button></center>
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