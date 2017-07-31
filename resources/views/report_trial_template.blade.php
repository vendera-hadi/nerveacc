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
        @foreach($invoices as $invoice)
            <tr>
              <td>{{$invoice['coa_code']}}</td>
              <td>{{$invoice['coa_name']}}</td>
              <td style="text-align: right">{{($invoice['saldo_awal'] >= 0 ? number_format($invoice['saldo_awal'],2)  : '('.number_format(abs($invoice['saldo_awal']),2).')')}}</td>
              <td style="text-align: right">{{($invoice['debet'] >= 0 ? number_format($invoice['debet'],2)  : '('.number_format(abs($invoice['debet']),2).')')}}</td>
              <td style="text-align: right">{{($invoice['credit'] >= 0 ? number_format($invoice['credit'],2)  : '('.number_format(abs($invoice['credit']),2).')')}}</td>
              <td style="text-align: right">{{($invoice['saldo_akhir'] >= 0 ? number_format($invoice['saldo_akhir'],2)  : '('.number_format(abs($invoice['saldo_akhir']),2).')')}}</td>
            </tr>
        @endforeach   
    </tbody>
</table>