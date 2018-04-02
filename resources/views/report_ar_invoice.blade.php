<table class="table table-striped" width="100%"> 
    <thead>
      <tr>
        <th>No.Invoice</th>
        <th>No Kontrak</th>
        <th>Nama Tenant</th>
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
          <td><span style="float:right">{{$invoice['inv_amount']}}</span></td>
        </tr>

        <tr>
          <td>Cost Components</td>
          <td colspan="8">
            <table class="table" width="100%">
                <thead>
                  <tr>
                    <th>Cost</th>
                    <th></th>
                  </tr> 
                </thead>
                <tbody>
                    @foreach($invoice['details'] as $detail)
                    <tr>
                      <td>{!!$detail['invdt_note']!!}</td>
                      <td><span style="float:right">{{$detail['invdt_amount']}}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
          </td>
        </tr>
        @endforeach
        
        
  </tbody>
</table>