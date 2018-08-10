<table class="table table-striped" style="font-size: 10pt !important;">
    <thead>
      <tr>
        <th style="text-align: center;">No.Unit</th>
        <th style="text-align: center;">Unit Size</th>
        <th style="text-align: center;">Unit Floor</th>
        <th style="text-align: center;">VA Utilities</th>
        <th style="text-align: center;">VA Maintenance</th>
        <th style="text-align: center;">Electricity Meter</th>
        <th style="text-align: center;">Water Meter</th>
        <th style="text-align: center;">Owner Name</th>
        <th style="text-align: center;">Address</th>
        <th style="text-align: center;">Phone</th>
        <th style="text-align: center;">BAST Date</th>
        <th style="text-align: center;">BAST BY</th>
      </tr>
    </thead>
        <tbody>
          @foreach($invoices as $invoice)
        <tr style="text-align: left;">
          <td>{{$invoice['unit_code']}}</td>
          <td>{{$invoice['unit_sqrt']}}</td>
          <td>{{$invoice['floor_name']}}</td>
          <td>{{$invoice['va_utilities']}}</td>
          <td>{{$invoice['va_maintenance']}}</td>
          <td>{{$invoice['meter_listrik']}}</td>
          <td>{{$invoice['meter_air']}}</td>
          <td>{{$invoice['tenan_name']}}</td>
          <td>{{$invoice['tenan_address']}}</td>
          <td>{{$invoice['tenan_phone']}}</td>
          <td>{{$invoice['contr_bast_date']}}</td>
          <td>{{$invoice['contr_bast_by']}}</td>
        </tr>
        @endforeach

  </tbody>
</table>