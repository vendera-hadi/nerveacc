<table class="table table-striped" width="100%"> 
    <thead>
      <tr>
        <th>Payment Date</th>
        <th>Payment Type</th>
        <th>Bank</th>
        <th>No Giro</th>
        <th>Invoice</th>
        <th>Amount</th>    
      </tr>
    </thead>
        <tbody>
            @foreach($payments as $paym)
            <tr>
                <td>{{date('d/m/Y',strtotime($paym->invpayh_date))}}</td>
                <td>{{$paym->paymtp_name}}</td>
                <td>{{$paym->cashbk_name}}</td>
                <td>{{$paym->invpayh_checkno}}</td>
                <td>{{$paym->inv_number}}</td>
                <td style="text-align: right;">{{"Rp. ".number_format($paym->invpayd_amount,2)}}</td>
            </tr>
            @endforeach
        </tbody>
</table>