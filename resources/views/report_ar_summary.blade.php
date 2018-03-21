<table class="table table-stripped" width="100%">
    <thead>
      <tr>
        <th>Tgl. JT</th>
        <th>No. Invoice</th>
        <th>Nilai Invoice</th>
        <th>Tgl Bayar</th>
        <th>No. Bayar</th>
        <th>Pembayaran</th>
        <th>Overdue</th>
        <th>Keterlambatan (Hari)</th>
        <th>Denda</th>
      </tr>
    </thead>
    <tbody>
    <?php
        $total_inv = 0; $total_bayar = 0; $total_sisa = 0; $total_denda = 0;
    ?>
        @foreach($invoices as $inv)
            @php
            $kwitansi = $paydate = $checkno = $payamount = [];
            @endphp
            @foreach($inv->paymentdtl as $paymdtl)
                @if(!$paymdtl->paymenthdr->status_void)
                    @php
                    $kwitansi[] = $paymdtl->paymenthdr->no_kwitansi;
                    $paydate[] = $paymdtl->paymenthdr->invpayh_date;
                    $checkno[] = $paymdtl->paymenthdr->invpayh_checkno;
                    $payamount[] = $paymdtl->invpayd_amount;
                    @endphp
                @endif
            @endforeach
        <tr>
            <td>{{date('d-m-Y',strtotime($inv->inv_duedate))}}</td>
            <td>{{$inv->inv_number}}</td>
            <td style="text-align: right">Rp {{number_format($inv->inv_amount)}}</td>
            <td>
                @foreach($paydate as $val)
                    {{ $val }}<br>
                @endforeach
            </td>
            <td>
                @foreach($kwitansi as $val)
                    {{ $val }}<br>
                @endforeach
            </td>

            <td style="text-align: right">
                @foreach($payamount as $val)
                    @php
                    $total_bayar += $val;
                    @endphp
                    @if(!empty($val))
                    Rp {{ number_format($val) }}<br>
                    @endif
                @endforeach
            </td>
            <td style="text-align: right">{{ number_format($inv->inv_outstanding) }}</td>
            <?php
                if(count($paydate) <= 0){
                    // echo "denda full";
                    // kalau tidak ada pembayaran denda
                    $date1=date_create(date('Y-m-d'));
                    $date2=date_create($inv->inv_duedate);
                    $diff=date_diff($date1,$date2);
                    // $hari = $diff->format("%a") - 7;
                    $hari = $diff->format("%a");
                    $denda = 1/1000 * $hari * $inv->inv_amount;
                    echo '<td style="text-align: right">'.($hari < 0 ? '-' : $hari).'</td>';
                    echo '<td style="text-align: right">'.($hari < 0 ? '0' : number_format($denda)).'</td>';
                }else{
                    // echo "denda gada";
                    $hari = $denda = 0;
                    if($paydate[0] > $inv->inv_duedate){
                        $date1=date_create($inv->inv_payh_date);
                        $date2=date_create($paydate[0]);
                        $diff=date_diff($date1,$date2);
                        $hari = $diff->format("%a");
                        $denda = 1/1000 * $hari * $inv->inv_amount;
                        echo '<td style="text-align: right">'.$hari.'</td>';
                        echo '<td style="text-align: right">'.number_format($denda).'</td>';
                    }
                }
                $total_inv = $total_inv + $inv->inv_amount;
                $total_sisa = $total_sisa + $inv->inv_outstanding;
                // $total_bayar = $total_bayar + $inv->inv_outstanding + ($hari < 0 ? 0 : $denda);
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
        <td>Denda</td>
        <td>TOTAL</td>
        <td style="text-align: right;">Jakarta, <?php echo date('d F Y') ?></td>
    </tr>
    <tr style="height: 50px; text-align: center; font-weight: bold;">
        <td>@if(!empty(@$current)){{number_format($current->ag30)}}@endif</td>
        <td>@if(!empty(@$current)){{number_format($current->ag60)}}@endif</td>
        <td>@if(!empty(@$current)){{number_format($current->ag90)}}@endif</td>
        <td>@if(!empty(@$current)){{number_format($current->agl180)}}@endif</td>
        <td>{{number_format($total_denda)}}</td>
        <td>@if(!empty(@$current)){{number_format($current->total + $total_denda)}}@endif</td>
        <td rowspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="5" style="font-weight: bold;"># {{terbilang($current->total + $total_denda)}} #</td>
    </tr>
    <tr>
        <td colspan="6">* = Tagihan Yang sudah Jatuh Tempo</td>
    </tr>
</table>