<table class="table table-bordered" width="100%"> 
    <thead>
      <tr>
        <th>Billing Info Number</th>
        <th>Tenant Name</th>
        <th>Unit Code</th>
        <th>Total Outstanding</th>    
      </tr>
    </thead>
        <tbody>
            @foreach($invoices as $invoice)
            <tr>
                <td>{{$invoice->contr_code}}</td>
                <td>{{$invoice->tenan_name}}</td>
                <td>{{$invoice->unit_name}}</td>
                <td style="text-align: right;">{{"Rp. ".number_format($invoice->outstanding,2)}}</td>
            </tr>
            @endforeach
        </tbody>
</table>