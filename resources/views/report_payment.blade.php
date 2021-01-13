<table class="table table-bordered" width="100%"> 
    <thead>
      <tr>
        <th>Payment</th>
        <th>Posting At</th>
        <th>No Kwitansi</th>
        <th>No Invoice</th>
        <th>Unit</th>
        <th>Nama</th>
        <th>Bank</th>
        <th>No Giro</th>
        <th>Amount</th> 
      </tr>
    </thead>
        <tbody>
        <?php $total_debet = 0; $total_kredit = 0; ?>
            @foreach($payments as $paym)
            <tr>
                <td>{{$paym->invpayh_date}}</td>
                <td>{{$paym->posting_at}}</td>
                <td>{{$paym->no_kwitansi}}</td>
                <td>{{$paym->inv_number}}</td>
                <td>{{$paym->unit_name}}</td>
                <td>{{$paym->tenan_name}}</td>
                <td>{{$paym->cashbk_name}}</td>
                <td>{{$paym->invpayh_checkno}}</td>
                <td style="text-align: right;">{{"Rp. ".number_format($paym->invpayd_amount,2)}}</td>
            </tr>
            <?php $total_debet = $total_debet + $paym->invpayd_amount; ?>
            @endforeach
        </tbody>
    <tfoot>
        <tr>
            <td colspan="8" style="text-align: right;">TOTAL</td>
            <td style="text-align: right;">{{"Rp. ".number_format($total_debet,2)}}</td>
        </tr>
    </tfoot>
</table>