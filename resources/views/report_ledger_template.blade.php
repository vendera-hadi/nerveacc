<table class="table table-bordered table-striped" width="100%">
    <tr>
        <th style="text-align: center;">Tanggal</th>
        <th style="text-align: center;">Kel.Jurnal</th>
        <th style="text-align: center;">COA</th>
        <th style="text-align: center;">Ref</th> 
        <th style="text-align: center;">Deskripsi</th> 
        <th style="text-align: center;">DEBET</th>
        <th style="text-align: center;">KREDIT</th>
        <th style="text-align: center;">Saldo</th> 
    </tr>
    <tbody>
        @foreach($invoices as $invoice)
        <tr>
            <td colspan="7">{{$invoice['coa']}}</td>
        </tr>
            <?php $total_debet = 0; $total_credit = 0; $total_saldo_akhir = 0; ?>
            @foreach($invoice['details'] as $detail)
            <tr>
              <td style="min-width: 100px;">{{($detail['ledg_date'] != '' ? date('d-M-y',strtotime($detail['ledg_date'])) : '')}}</td>
              <td style="text-align: center">{{$detail['jour_type_prefix']}}</td>
              <td>{{$detail['coa_code']}}</td>
              <td>{{$detail['ledg_refno']}}</td>
              <td>{{$detail['ledg_description']}}</td>
              <td style="text-align: right">{{($detail['ledg_debit'] >= 0 ? number_format($detail['ledg_debit'],2)  : '('.number_format(abs($detail['ledg_debit']),2).')')}}</td>
              <td style="text-align: right">{{($detail['ledg_credit'] >= 0 ? number_format($detail['ledg_credit'],2)  : '('.number_format(abs($detail['ledg_credit']),2).')')}}</td>
              <td style="text-align: right">{{($detail['saldo_akhir'] >= 0 ? number_format($detail['saldo_akhir'],2)  : '('.number_format(abs($detail['saldo_akhir']),2).')')}}</td>
            </tr>
            <?php 
                $total_debet = $total_debet +  $detail['ledg_debit'];
                $total_credit = $total_credit + $detail['ledg_credit'];
                $total_saldo_akhir =  $detail['saldo_akhir'];
            ?>
            @endforeach
        <tr>
            <td colspan="5" style="text-align: right; font-weight: bold;">TOTAL</td>
            <td style="text-align: right; font-weight: bold;">{{($total_debet >= 0 ? number_format($total_debet,2) : '('.number_format(abs($total_debet),2).')')}}</td>
            <td style="text-align: right; font-weight: bold;">{{($total_debet >= 0 ? number_format($total_credit,2) : '('.number_format(abs($total_credit),2).')')}}</td>
            <td style="text-align: right; font-weight: bold;">{{($total_debet >= 0 ? number_format($total_saldo_akhir,2) : '('.number_format(abs($total_saldo_akhir),2).')')}}</td>
        </tr>
        <tr><td colspan="8">&nbsp;</td></tr>
        @endforeach    
    </tbody>
</table>