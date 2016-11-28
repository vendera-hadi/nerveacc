<center>
  <h3>Input Last Meter</h3>
  <p>Contract No : <strong>{{$contInv[0]->contr_no}}</strong></p>
  <p>Unit : <strong>{{$contInv[0]->unit_code}}</strong></p>
</center>

<form method="POST" id="formEditMeter">
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
      @foreach($contInv as $cinv)
      <tr class="text-center">
        <input type="hidden" name="meter_start[]" value="">
        <input type="hidden" name="meter_rate[]" value="{{$cinv->costd_rate}}">
        <input type="hidden" name="meter_burden[]" value="{{$cinv->costd_burden}}">
        <input type="hidden" name="meter_admin[]" value="{{$cinv->costd_admin}}">
        <td>{{$cinv->costd_name}}</td>
        <td>meter start</td>
        <td>
          <input type="text" name="meter_end[]" class="numeric meter_e" value="meter end">
        </td>
        <td>meter used</td>
        <td>{{"Rp. ".number_format($cinv->costd_rate)}}</td>
        <td>{{"Rp. ".number_format($cinv->costd_burden)}}</td>
        <td>{{"Rp. ".number_format($cinv->costd_admin)}}</td>
      </tr>
      @endforeach
  </table>
</div>
</form>