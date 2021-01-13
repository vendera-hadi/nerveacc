<table class="table table-bordered" width="100%"> 
    <thead>
      <tr>
        <th>No.Unit</th>
        <th>Luas</th>
        <th>Nama Tenant</th>
        <th>No.Invoice</th>  
        <th>Tgl</th>
        <th>Tempo</th>
        <th>Amount</th>
      </tr>
    </thead>
        <tbody>
        @foreach($invoices as $invoice)
          <tr>
            <td>{{$invoice['unit_code']}}</td>
            <td>{{$invoice['unit_sqrt']}}</td>
            <td>{{$invoice['tenan_name']}}</td>
            <td>{{$invoice['inv_number']}}</td>
            <td>{{$invoice['inv_date']}}</td>
            <td>{{$invoice['inv_duedate']}}</td>
            <td>{{$invoice['inv_amount']}}</td>
          </tr>
        @foreach($invoice['details'] as $detail)
        <tr>
          <td colspan="6">{!!$detail['invdt_note']!!}</td>
          <td>{{$detail['invdt_amount']}}</td>
        </tr>
        @endforeach  
      @endforeach
        
        
  </tbody>
</table>