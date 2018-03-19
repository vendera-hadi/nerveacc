<table class="table table-stripped" width="100%"> 
    <thead>
      <tr>
        <th>Tgl. JT</th>
        <th>No. Invoice</th>
        <th>Nilai Invoice</th>
        <th>No. Bayar</th>
        <th>Tgl Bayar</th>
        <th>No. Giro</th>
        <th>Nilai Bayar Koreksi</th> 
        <th>Sisa</th>
        <th>Hari</th>
        <th>Denda</th>
      </tr>
    </thead>
    <tbody>
    <?php $total_inv = 0; $total_bayar = 0; $total_sisa = 0; $total_denda = 0;?>
        @foreach($invoices as $inv)
        <tr>
            <td>{{$inv->jatuhtempo}}</td>
            <td>{{$inv->inv_number}}</td>
            <td style="text-align: right">{{number_format($inv->inv_amount)}}</td>
            <td>{{$inv->no_kwitansi}}</td>
            <td>{{$inv->tanggalbayar}}</td>
            <td>{{$inv->invpayh_checkno}}</td>
            <td style="text-align: right">{{number_format($inv->invpayd_amount)}}</td>
            <td style="text-align: right">{{number_format($inv->inv_outstanding)}}</td>
            <?php 
                if($inv->invpayh_date == NULL){
                    $date1=date_create(date('Y-m-d'));
                    $date2=date_create($inv->inv_duedate);
                    $diff=date_diff($date1,$date2);
                    $hari = $diff->format("%a") - 7;
                    $denda = 1/1000 * $hari * $inv->inv_amount;
                    echo '<td style="text-align: right">'.($hari < 0 ? '-' : $hari).'</td>';
                    echo '<td style="text-align: right">'.($hari < 0 ? '0' : number_format($denda)).'</td>';
                }else{
                    $date1=date_create($inv->inv_payh_date);
                    $date2=date_create($inv->inv_duedate);
                    $diff=date_diff($date1,$date2);
                    $hari = $diff->format("%a");
                    $denda = 1/1000 * $hari * $inv->inv_amount;
                    echo '<td style="text-align: right">'.$hari.'</td>';
                    echo '<td style="text-align: right">'.number_format($denda).'</td>';
                }
                $total_inv = $total_inv + $inv->inv_amount;
                $total_bayar = $total_bayar + $inv->inv_outstanding + ($hari < 0 ? 0 : $denda);
                $total_sisa = $total_sisa + $inv->inv_outstanding;
                $total_denda = $total_denda + ($hari < 0 ? 0 : $denda);
            ?>

        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="font-weight: bold;">
            <td colspan="2" style="text-align: center;">TOTAL</td>
            <td style="text-align: right;"><?php echo number_format($total_inv) ?></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td style="text-align: right;"><?php echo number_format($total_bayar) ?></td>
            <td style="text-align: right;"><?php echo number_format($total_sisa) ?></td>
            <td>&nbsp;</td>
            <td style="text-align: right;"><?php echo number_format($total_denda) ?></td>
        </tr>
    </tfoot>
</table>
<br>
<table class="table table-bordered" width="100%">
    <tr style="font-weight: bold;">
        <td>Tunggak 1 - 30 Hari</td>
        <td>Tunggak 31 - 60 Hari</td>
        <td>Tunggak 60 - 90 Hari</td>
        <td>Over 180 Hari</td>
        <td>TOTAL</td>
        <td style="text-align: right;">Jakarta, <?php echo date('d F Y') ?></td>
    </tr>
    <tr style="height: 50px; text-align: center; font-weight: bold;">
        <td>{{number_format($current[0]->ag30)}}</td>
        <td>{{number_format($current[0]->ag60)}}</td>
        <td>{{number_format($current[0]->ag90)}}</td>
        <td>{{number_format($current[0]->agl180)}}</td>
        <td>{{number_format($current[0]->total)}}</td>
        <td rowspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="5" style="font-weight: bold;"># {{$terbilang}} #</td>
    </tr>
    <tr>
        <td colspan="6">* = Tagihan Yang sudah Jatuh Tempo</td>
    </tr>
</table>