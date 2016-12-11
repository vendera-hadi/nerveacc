<table class="table table-bordered" width="100%"> 
    <thead>
      <tr>
        <th>No.Invoice</th>
        <th>No Kontrak</th>
        <th>Nama Tenan</th>
        <th>Unit</th>  
        <th>Tgl Invoice</th>
        <th>Jatuh Tempo</th>
        <th>Jenis Invoice</th>
        <th>Amount</th>
        
      </tr>
    </thead>
        <tbody>
          @foreach($invoices as $invoice)
        <tr>
          <td>{{$invoice['inv_number']}}</td>
          <td>{{$invoice['contr_no']}}</td>
          <td>{{$invoice['tenan_name']}}</td>
          <td>{{$invoice['unit_name']}}</td>
          <td>{{$invoice['inv_date']}}</td>
          <td>{{$invoice['inv_duedate']}}</td>
          
          <td>{{$invoice['invtp_name']}}</td>
          <td>{{$invoice['inv_amount']}}</td>
        </tr>

        <tr>
          <td>Details</td>
          <td colspan="8">
            <table class="table" width="100%">
                <thead>
                  <tr>
                    <th>Note</th>
                    <th>Start</th>
                    <th>End</th>  
                    <th>Consumption</th>
                    <th>Amount</th>
                  </tr> 
                </thead>
                <tbody>
                    @foreach($invoice['details'] as $detail)
                    <tr>
                      <td>{!!$detail['invdt_note']!!}</td>
                      <td>{{$detail['meter_start']}}</td>
                      <td>{{$detail['meter_end']}}</td>
                      <td>{{$detail['meter_used']}}</td>
                      <td>{{$detail['invdt_amount']}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
          </td>
        </tr>
        @endforeach
        
        
  </tbody>
</table>