<h4><?php if($ty == 1){ echo 'Outstanding Invoice'; }else{ echo 'Paid Invoice'; } ?></h4>
<table class="table table-striped" width="100%"> 
    <thead>
      <tr>
        <th>Unit</th>
        <th>Tenant</th>
        <th>Total</th>   
        <th>{{$label[0]}}</th>
        <th>{{$label[1]}}</th> 
        <th>{{$label[2]}}</th>
        <th>{{$label[3]}}</th>     
      </tr>
    </thead>
        <tbody>
            @foreach($invoices as $invoice)
            <tr>
                <td>{{$invoice->unit_code}}</td>
                <td>{{$invoice->tenan_name}}</td>
                <td>{{"Rp. ".number_format($invoice->total,2)}}</td>
                <td>{{"Rp. ".number_format($invoice->ag30,2)}}</td>
                <td>{{"Rp. ".number_format($invoice->ag60,2)}}</td>
                <td>{{"Rp. ".number_format($invoice->ag90,2)}}</td>
                <td>{{"Rp. ".number_format($invoice->agl180,2)}}</td>
            </tr>
            @endforeach
        </tbody>
</table>