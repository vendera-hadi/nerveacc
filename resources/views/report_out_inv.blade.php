<table class="table table-bordered" width="100%"> 
    <thead>
      <tr>
        <th>Invoice Number</th>
        <th>Invoice Date</th>
        <th>Invoice Due Date</th>
        <th>Tenant Name</th>
        <th>Unit Code</th>
        <th>Total Outstanding</th>    
      </tr>
    </thead>
        <tbody>
            @foreach($invoices as $invoice)
            <tr>
                <td>{{$invoice->inv_number}}</td>
                <td>{{$invoice->inv_date}}</td>
                <td>{{$invoice->inv_duedate}}</td>
                <td>{{$invoice->tenan_name}}</td>
                <td>{{$invoice->unit_name}}</td>
                <td style="text-align: right;">{{"Rp. ".number_format($invoice->inv_outstanding,2)}}</td>
            </tr>
            @endforeach
        </tbody>
</table>