<table class="table table-bordered table-striped" width="100%">
    <tr>
        <th style="text-align: center;">Account</th>
        <th style="text-align: center;">Nama Account</th>
        <th style="text-align: center;">Saldo Awal</th>
        <th style="text-align: center;">Debet</th>
        <th style="text-align: center;">Credit</th>
        <th style="text-align: center;">Saldo Akhir</th> 
    </tr>
    <tbody>
        <?php $total_saldo_awal = 0; $total_debet = 0; $total_credit = 0; $total_saldo_akhir = 0; ?>
        @foreach($invoices as $invoice)
            <tr>
              <td>{{$invoice['coa_code']}}</td>
              <td>{{$invoice['coa_name']}}</td>
              <td style="text-align: right">{{number_format($invoice['saldo_awal'],2)}}</td>
              <td style="text-align: right">{{number_format($invoice['debet'],2)}}</td>
              <td style="text-align: right">{{number_format($invoice['credit'],2)}}</td>
              <td style="text-align: right">{{number_format($invoice['saldo_akhir'],2)}}</td>
            </tr>
        <?php 
                $total_saldo_awal = $total_saldo_awal +  $invoice['saldo_awal'];
                $total_debet = $total_debet +  $invoice['debet'];
                $total_credit = $total_credit + $invoice['credit'];
                $total_saldo_akhir = $total_saldo_akhir + $invoice['saldo_akhir'];
            ?>
        @endforeach
        <tr>
            <td colspan="2" style="text-align: right; font-weight: bold;">TOTAL</td>
            <td style="text-align: right; font-weight: bold;">{{number_format($total_saldo_awal,2)}}</td>
            <td style="text-align: right; font-weight: bold;">{{number_format($total_debet,2)}}</td>
            <td style="text-align: right; font-weight: bold;">{{number_format($total_credit,2)}}</td>
            <td style="text-align: right; font-weight: bold;">{{number_format($total_saldo_akhir,2)}}</td>
        </tr>    
    </tbody>
</table>