<table class="table table-stripped" width="100%"> 
    <thead>
      <tr>
        <th>No Invoice / No Kwitansi</th>
        <th>Tgl Invoice / Tgl Payment</th>
        <th>Unit</th>
        <th>Bank</th>
        <th>No Giro</th>
        <th>Debet</th>
        <th>Kredit</th> 
      </tr>
    </thead>
        <tbody>
        <?php $total_debet = 0; $total_kredit = 0; ?>
            @foreach($payments as $paym)
            <tr>
                <td>{{$paym->no_kwitansi}}</td>
                <td>{{date('d/m/Y',strtotime($paym->invpayh_date))}}</td>
                <td>{{$paym->unit_name}}</td>
                <td>{{$paym->cashbk_name}}</td>
                <td>{{$paym->invpayh_checkno}}</td>
                <td style="text-align: right;">{{"Rp. ".number_format($paym->invpayd_amount,2)}}</td>
                <td>&nbsp;</td>
            </tr>
            <?php $total_debet = $total_debet + $paym->invpayd_amount; ?>
            @endforeach
            @foreach($inv as $invm)
            <tr>
                <td>{{$invm->inv_number}}</td>
                <td>{{date('d/m/Y',strtotime($invm->inv_date))}}</td>
                <td>{{$invm->unit_name}}</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td style="text-align: right;">{{"Rp. ".number_format($invm->inv_amount,2)}}</td> 
            </tr>
            <?php $total_kredit = $total_kredit + $invm->inv_amount; ?>
            @endforeach
        </tbody>
    <tfoot>
        <tr>
            <td colspan="5" style="text-align: right;">TOTAL</td>
            <td style="text-align: right;">{{"Rp. ".number_format($total_debet,2)}}</td>
            <td style="text-align: right;">{{"Rp. ".number_format($total_kredit,2)}}</td>
        </tr>
    </tfoot>
</table>