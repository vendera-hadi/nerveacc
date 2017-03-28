<table class="table table-striped" width="100%"> 
    <thead>
      <tr>
        <th>Tenan</th>
        <th>Total</th>
        <th>Current</th>
        <th>Due Date</th>   
        <th>1 - 30 Hari</th>
        <th>31 - 60 Hari</th> 
        <th>61 - 90 Hari</th>
        <th>> 90 Hari</th>     
      </tr>
    </thead>
        <tbody>
            @foreach($invoices as $invoice)
            <tr>
                <td>{{$invoice->gabung}}</td>
                <td>{{"Rp. ".number_format($invoice->total,2)}}</td>
                <td>{{"Rp. ".number_format($invoice->current,2)}}</td>
                <td>{{date('d/m/Y',strtotime($invoice->inv_duedate))}}</td>
                <td>{{"Rp. ".number_format($invoice->ag30,2)}}</td>
                <td>{{"Rp. ".number_format($invoice->ag60,2)}}</td>
                <td>{{"Rp. ".number_format($invoice->ag90,2)}}</td>
                <td>{{"Rp. ".number_format($invoice->agl180,2)}}</td>
            </tr>
            @endforeach
        </tbody>
</table>