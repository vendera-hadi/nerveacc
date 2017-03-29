<table class="table table-striped" style="font-size: 10pt !important;"> 
    <thead>
      <tr>
        <th style="text-align: center;">No.Unit</th>
        <th style="text-align: center;">Unit Size</th>
        <th style="text-align: center;">Unit Floor</th>
        <th style="text-align: center;">Virtual Account</th>  
        <th style="text-align: center;">Electricity Meter</th>
        <th style="text-align: center;">Water Meter</th>
        <th style="text-align: center;">Owner Name</th>
        <th style="text-align: center;">NIP</th>
        <th style="text-align: center;">Phone</th>
      </tr>
    </thead>
        <tbody>
          @foreach($invoices as $invoice)
        <tr style="text-align: center;">
          <td>{{$invoice['unit_code']}}</td>
          <td>{{$invoice['unit_sqrt']}}</td>
          <td>{{$invoice['floor_name']}}</td>
          <td>{{$invoice['virtual_account']}}</td>
          <td>{{$invoice['meter_listrik']}}</td>
          <td>{{$invoice['meter_air']}}</td>
          <td>{{$invoice['tenan_name']}}</td>
          <td>{{$invoice['tenan_idno']}}</td>
          <td>{{$invoice['tenan_phone']}}</td>
        </tr>
        @endforeach
        
  </tbody>
</table>